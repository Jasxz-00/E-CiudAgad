<?php

namespace App\Rules;

use App\Services\IdValidationService;
use Closure;
use cv\Mat;
use cv\Scalar;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidateGovernmentID implements ValidationRule
{
    protected string $idType;

    protected string $firstName;

    protected string $lastName;

    protected ?string $addressText;

    protected float $minClarity;

    protected float $minOcrConfidence;

    protected array $extracted = [];

    protected array $messages = [];

    public function __construct(
        string $idType,
        string $firstName,
        string $lastName,
        ?string $addressText = null,
        float $minClarity = 15.0,
        float $minOcrConfidence = 30.0
    ) {
        $this->idType = $idType;
        $this->firstName = strtoupper(trim($firstName));
        $this->lastName = strtoupper(trim($lastName));
        $this->addressText = $addressText ? strtoupper(trim($addressText)) : null;
        $this->minClarity = $minClarity;
        $this->minOcrConfidence = $minOcrConfidence;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail(__('The ID scan file is invalid.'));

            return;
        }

        $tempPath = $value->getRealPath();
        if (! $tempPath || ! file_exists($tempPath)) {
            $fail(__('Cannot access the uploaded file.'));

            return;
        }

        $clarityCheck = $this->checkClarity($tempPath);
        if (! $clarityCheck['passed']) {
            $fail($clarityCheck['message']);

            return;
        }

        $ocrResult = $this->performOcr($tempPath);
        if (! $ocrResult['success']) {
            if (! empty($ocrResult['engine_unavailable'])) {
                return;
            }
            $fail($ocrResult['message']);

            return;
        }

        $text = $ocrResult['text'];
        $confidence = $ocrResult['confidence'];
        $this->extracted = $ocrResult['extracted'];

        if ($confidence < $this->minOcrConfidence) {
            $fail(__('ID image is not clear enough. Confidence: :confidence%. Minimum required: :min%.', [
                'confidence' => round($confidence, 1),
                'min' => $this->minOcrConfidence,
            ]));

            return;
        }

        $idValid = $this->validateIdNumber($text);
        if (! $idValid) {
            $fail(__('Could not read a valid ID number from the scanned image. Please ensure the ID is clearly visible.'));

            return;
        }

        $nameCheck = $this->crossCheckName($text);
        if (! $nameCheck['passed']) {
            $fail($nameCheck['message']);

            return;
        }

        if ($this->addressText) {
            $addressCheck = $this->crossCheckAddress($text);
            if (! $addressCheck['passed']) {
                $fail($addressCheck['message']);

                return;
            }
        }
    }

    public function getExtractedData(): array
    {
        return $this->extracted;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    protected function checkClarity(string $imagePath): array
    {
        if (extension_loaded('opencv')) {
            return $this->checkClarityOpenCv($imagePath);
        }

        return $this->checkClarityGd($imagePath);
    }

    protected function checkClarityOpenCv(string $imagePath): array
    {
        try {
            $mat = \cv\imread($imagePath);
            if ($mat === null || $mat->empty()) {
                return ['passed' => false, 'message' => __('Could not read the ID image for processing.')];
            }

            $gray = new Mat;
            \cv\cvtColor($mat, $gray, \cv\COLOR_BGR2GRAY);

            $laplacian = new Mat;
            \cv\Laplacian($gray, $laplacian, \cv\CV_64F);

            $mean = new Scalar;
            $stddev = new Scalar;
            \cv\meanStdDev($laplacian, $mean, $stddev);

            $variance = $stddev->val[0] * $stddev->val[0];

            if ($variance < $this->minClarity) {
                return [
                    'passed' => false,
                    'message' => __('The ID image appears blurry or unclear. Please upload a sharper image. (Clarity score: :score)', ['score' => round($variance, 1)]),
                ];
            }

            return ['passed' => true, 'variance' => $variance];
        } catch (\Throwable $e) {
            return ['passed' => false, 'message' => __('Error processing image: :error', ['error' => $e->getMessage()])];
        }
    }

    protected function checkClarityGd(string $imagePath): array
    {
        try {
            $info = @getimagesize($imagePath);
            if (! $info) {
                return ['passed' => false, 'message' => __('Could not read the ID image.')];
            }

            $image = match ($info[2]) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($imagePath),
                IMAGETYPE_PNG => @imagecreatefrompng($imagePath),
                IMAGETYPE_GIF => @imagecreatefromgif($imagePath),
                default => false,
            };

            if (! $image) {
                return ['passed' => false, 'message' => __('Unsupported image format.')];
            }

            $width = imagesx($image);
            $height = imagesy($image);

            if ($width < 300 || $height < 200) {
                imagedestroy($image);

                return ['passed' => false, 'message' => __('ID image resolution is too low. Minimum 300x200 pixels required.')];
            }

            $grayValues = [];
            for ($y = 0; $y < min(100, $height); $y += 4) {
                for ($x = 0; $x < min(100, $width); $x += 4) {
                    $rgb = imagecolorat($image, (int) ($x * $width / 100), (int) ($y * $height / 100));
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    $grayValues[] = (int) (0.299 * $r + 0.587 * $g + 0.114 * $b);
                }
            }
            imagedestroy($image);

            $n = count($grayValues);
            if ($n < 10) {
                return ['passed' => false, 'message' => __('Could not analyze the ID image.')];
            }

            $mean = array_sum($grayValues) / $n;
            $variance = 0;
            foreach ($grayValues as $v) {
                $variance += ($v - $mean) ** 2;
            }
            $variance /= $n;

            if ($variance < $this->minClarity * 10) {
                return [
                    'passed' => false,
                    'message' => __('The ID image appears blurry or unclear. Please upload a sharper image.'),
                ];
            }

            return ['passed' => true, 'variance' => $variance];
        } catch (\Throwable $e) {
            return ['passed' => false, 'message' => __('Error processing image: :error', ['error' => $e->getMessage()])];
        }
    }

    protected function performOcr(string $imagePath): array
    {
        $tesseractBinary = $this->findTesseract();

        if ($tesseractBinary) {
            return $this->runTesseract($imagePath, $tesseractBinary);
        }

        if (extension_loaded('opencv')) {
            return $this->runOcrOpenCv($imagePath);
        }

        return [
            'success' => false,
            'engine_unavailable' => true,
            'message' => __('OCR engine (Tesseract) is not available on the server. Please contact support.'),
        ];
    }

    protected function findTesseract(): ?string
    {
        $candidates = [
            'tesseract',
            'tesseract.exe',
            'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
            'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
            '/usr/bin/tesseract',
            '/usr/local/bin/tesseract',
        ];

        foreach ($candidates as $binary) {
            if (str_contains($binary, '\\') || str_contains($binary, '/')) {
                if (file_exists($binary)) {
                    return $binary;
                }
            } else {
                $output = null;
                $code = null;
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    exec("where {$binary} 2>NUL", $output, $code);
                } else {
                    exec("which {$binary} 2>/dev/null", $output, $code);
                }
                if ($code === 0 && ! empty($output[0])) {
                    return $output[0];
                }
            }
        }

        return null;
    }

    protected function runTesseract(string $imagePath, string $binary): array
    {
        $tempDir = sys_get_temp_dir();
        $tempBase = $tempDir.DIRECTORY_SEPARATOR.'ocr_'.uniqid('id_', true);
        $tempImage = $tempBase.'.png';
        $tempOutput = $tempBase;

        try {
            $info = @getimagesize($imagePath);
            if (! $info) {
                return ['success' => false, 'message' => __('Cannot read image properties.')];
            }

            $srcImage = match ($info[2]) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($imagePath),
                IMAGETYPE_PNG => @imagecreatefrompng($imagePath),
                default => null,
            };

            if (! $srcImage) {
                return ['success' => false, 'message' => __('Unsupported image format for OCR.')];
            }

            $width = imagesx($srcImage);
            $height = imagesy($srcImage);
            $maxDim = 2048;
            if ($width > $maxDim || $height > $maxDim) {
                $scale = min($maxDim / $width, $maxDim / $height);
                $newW = (int) ($width * $scale);
                $newH = (int) ($height * $scale);
                $resized = imagecreatetruecolor($newW, $newH);
                imagecopyresampled($resized, $srcImage, 0, 0, 0, 0, $newW, $newH, $width, $height);
                imagedestroy($srcImage);
                $srcImage = $resized;
            }

            imagepng($srcImage, $tempImage);
            imagedestroy($srcImage);

            $escapedImage = escapeshellarg($tempImage);
            $escapedOutput = escapeshellarg($tempOutput);
            $escapedBinary = escapeshellcmd($binary);

            $command = "{$escapedBinary} {$escapedImage} {$escapedOutput} --psm 6 --oem 1 -l eng 2>&1";
            exec($command, $outputLines, $returnCode);

            $txtFile = $tempOutput.'.txt';
            if ($returnCode !== 0 || ! file_exists($txtFile)) {
                @unlink($tempImage);

                return ['success' => false, 'message' => __('Failed to process ID image with OCR.')];
            }

            $text = file_get_contents($txtFile);
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

            $confidence = $this->estimateConfidence($text);

            @unlink($tempImage);
            @unlink($txtFile);

            return [
                'success' => true,
                'text' => strtoupper(trim($text)),
                'confidence' => $confidence,
                'extracted' => $this->extractFields(strtoupper(trim($text))),
            ];
        } catch (\Throwable $e) {
            @unlink($tempImage ?? '');
            @unlink(($tempOutput ?? '').'.txt');

            return ['success' => false, 'message' => __('OCR processing error: :error', ['error' => $e->getMessage()])];
        }
    }

    protected function runOcrOpenCv(string $imagePath): array
    {
        try {
            $mat = \cv\imread($imagePath);
            if ($mat === null || $mat->empty()) {
                return ['success' => false, 'message' => __('Could not read the ID image.')];
            }

            $gray = new Mat;
            \cv\cvtColor($mat, $gray, \cv\COLOR_BGR2GRAY);

            $binary = new Mat;
            \cv\threshold($gray, $binary, 0, 255, \cv\THRESH_BINARY | \cv\THRESH_OTSU);

            $tempDir = sys_get_temp_dir();
            $tempPath = $tempDir.DIRECTORY_SEPARATOR.'ocv_'.uniqid('id_', true).'.png';
            \cv\imwrite($tempPath, $binary);

            $result = $this->runTesseract($tempPath, $this->findTesseract() ?? 'tesseract');

            @unlink($tempPath);

            return $result;
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => __('OpenCV processing error: :error', ['error' => $e->getMessage()])];
        }
    }

    protected function validateIdNumber(string $text): bool
    {
        $service = app(IdValidationService::class);
        $pattern = $service->getPattern($this->idType);

        if ($pattern === null) {
            return true;
        }

        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $candidates = [
                trim($line),
                str_replace(' ', '', $line),
                preg_replace('/[^A-Z0-9-]/', '', $line),
            ];
            foreach ($candidates as $candidate) {
                if (preg_match($pattern, $candidate)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function crossCheckName(string $text): array
    {
        $nameFound = false;
        $bestScore = 0;
        $details = [];

        $nameVariants = [];
        $nameCombos = [
            "{$this->firstName} {$this->lastName}",
            "{$this->lastName}, {$this->firstName}",
            "{$this->firstName} {$this->lastName}",
        ];

        foreach ($nameCombos as $combo) {
            $parts = explode(' ', $combo);
            foreach ($parts as $part) {
                $nameVariants[] = $part;
            }
        }
        $nameVariants = array_unique($nameVariants);

        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) < 2) {
                continue;
            }

            foreach ($nameVariants as $variant) {
                if (strlen($variant) < 2) {
                    continue;
                }

                $distance = levenshtein($variant, $line);
                $maxLen = max(strlen($variant), strlen($line));
                $similarity = $maxLen > 0 ? (1 - $distance / $maxLen) : 0;

                if ($similarity > $bestScore) {
                    $bestScore = $similarity;
                }

                if ($similarity >= 0.7) {
                    $nameFound = true;
                    $details[] = ['matched' => $variant, 'line' => $line, 'score' => $similarity];
                }

                $wordsInLine = explode(' ', $line);
                foreach ($wordsInLine as $word) {
                    $word = trim($word);
                    if (strlen($word) < 2) {
                        continue;
                    }
                    $wordDist = levenshtein($variant, $word);
                    $wordMax = max(strlen($variant), strlen($word));
                    $wordSim = $wordMax > 0 ? (1 - $wordDist / $wordMax) : 0;
                    if ($wordSim >= 0.8) {
                        $nameFound = true;
                        $details[] = ['matched' => $variant, 'word' => $word, 'score' => $wordSim];
                    }
                }
            }
        }

        if ($bestScore < 0.3) {
            return [
                'passed' => false,
                'message' => __('The name on the ID does not match the name you entered. Please check and try again.'),
                'score' => $bestScore,
                'details' => $details,
            ];
        }

        if (! $nameFound && $bestScore < 0.6) {
            return [
                'passed' => false,
                'message' => __('Could not confirm your name on the ID. Please ensure the ID image is clear and readable.'),
                'score' => $bestScore,
                'details' => $details,
            ];
        }

        return [
            'passed' => true,
            'score' => $bestScore,
            'details' => $details,
        ];
    }

    protected function crossCheckAddress(string $text): array
    {
        if (strlen($this->addressText) < 5) {
            return ['passed' => true];
        }

        $addressWords = preg_split('/[\s,]+/', $this->addressText);
        $addressWords = array_filter($addressWords, fn ($w) => strlen($w) >= 3);
        $addressWords = array_unique($addressWords);

        if (empty($addressWords)) {
            return ['passed' => true];
        }

        $lines = explode("\n", $text);
        $matchesFound = 0;

        foreach ($addressWords as $addressWord) {
            foreach ($lines as $line) {
                $line = trim($line);
                if (strlen($line) < 2) {
                    continue;
                }

                $wordsInLine = explode(' ', $line);
                foreach ($wordsInLine as $word) {
                    $word = trim(preg_replace('/[^A-Z0-9]/', '', strtoupper($word)));
                    $cleanAddr = trim(preg_replace('/[^A-Z0-9]/', '', strtoupper($addressWord)));
                    if (strlen($word) < 2 || strlen($cleanAddr) < 2) {
                        continue;
                    }

                    $dist = levenshtein($word, $cleanAddr);
                    $maxLen = max(strlen($word), strlen($cleanAddr));
                    if ($maxLen > 0 && (1 - $dist / $maxLen) >= 0.75) {
                        $matchesFound++;
                        break 2;
                    }
                }
            }
        }

        $requiredMatches = max(1, (int) (count($addressWords) * 0.3));

        if ($matchesFound < $requiredMatches) {
            return [
                'passed' => false,
                'message' => __('The address on the ID does not match your entered address. Please verify the ID image.'),
                'matches' => $matchesFound,
                'required' => $requiredMatches,
            ];
        }

        return ['passed' => true, 'matches' => $matchesFound];
    }

    protected function extractFields(string $text): array
    {
        $fields = [];

        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/\b\d{4}-\d{4}-\d{4}-\d{4}\b/', $line, $m)) {
                $fields['id_number'] = $m[0];
            } elseif (preg_match('/\b[A-Z]\d{7}[A-Z]\b|\b[A-Z]{2}\d{7}\b/', $line, $m)) {
                $fields['id_number'] = $m[0];
            } elseif (preg_match('/\b\d{4}-\d{7}-\d\b/', $line, $m)) {
                $fields['id_number'] = $m[0];
            } elseif (preg_match('/\b\d{2}-\d{9}-\d\b/', $line, $m)) {
                $fields['id_number'] = $m[0];
            } elseif (preg_match('/\b[A-Z]\d{2}-\d{2}-\d{6}\b/', $line, $m)) {
                $fields['id_number'] = $m[0];
            } elseif (preg_match('/\b\d{7}\b/', $line, $m) && ! preg_match('/\b\d{4,6}\b/', $line)) {
                $fields['id_number'] = $m[0];
            }

            if (preg_match('/\b(NAME|FIRST NAME|LAST NAME|FAMILY NAME|SURNAME|GIVEN NAMES?)\s*[:\-]?\s*(.+)/i', $line, $m)) {
                $fields['name_field'][] = trim($m[2]);
            }

            if (preg_match('/\b(ADDRESS|ADDR|STREET|BARANGAY|CITY|MUNICIPALITY|PROVINCE)\s*[:\-]?\s*(.+)/i', $line, $m)) {
                $fields['address_field'][] = trim($m[2]);
            }
        }

        return $fields;
    }

    protected function estimateConfidence(string $text): float
    {
        $text = trim($text);
        if (empty($text)) {
            return 0;
        }

        $length = strlen($text);
        if ($length < 20) {
            return 15;
        }

        $printableRatio = 0;
        $printableChars = preg_match_all('/[A-Z0-9\s.,:;\-\/\(\)]/', $text);
        if ($length > 0) {
            $printableRatio = $printableChars / $length;
        }

        $gibberishScore = 0;
        $words = preg_split('/\s+/', $text);
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) > 15) {
                $gibberishScore++;
            }
            $consecutiveConsonants = 0;
            $maxConsonants = 0;
            for ($i = 0; $i < strlen($word); $i++) {
                if (! in_array($word[$i], ['A', 'E', 'I', 'O', 'U'])) {
                    $consecutiveConsonants++;
                    $maxConsonants = max($maxConsonants, $consecutiveConsonants);
                } else {
                    $consecutiveConsonants = 0;
                }
            }
            if ($maxConsonants > 8) {
                $gibberishScore++;
            }
        }

        $baseConfidence = $printableRatio * 100;
        $deduction = min(40, $gibberishScore * 5);
        $confidence = max(5, $baseConfidence - $deduction);

        return min(99, $confidence);
    }
}
