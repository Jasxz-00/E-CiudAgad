# Figure 2. Registration with Duplicate Account Detection

```mermaid
flowchart TD
    Start([Start]):::gray
    RegForm[Registration Form]:::purple
    DupCheck{Duplicate Account<br/>Check}:::orange
    NoMatch[No Match Found]:::green
    Match[Match Found]:::orange
    CreateAcc[Create Resident Account]:::purple
    Generate["Generate Tracking Number<br/>Generate PIN"]:::purple
    Dashboard[Resident Dashboard]:::gray
    ExistingMsg["Show: 'Account already exists'"]:::orange
    LoginOpt["Resident Options:<br/>• Login with Tracking # + PIN<br/>• Recover Account<br/>• Contact Staff"]:::green
    Insist["Resident insists<br/>no account exists"]:::orange
    StaffVerif[Barangay Staff<br/>Verification]:::orange
    StaffReview["Staff reviews:<br/>• Existing info<br/>• Encoding errors<br/>• Identity check"]:::orange
    Confirmed{Duplicate<br/>Confirmed?}:::orange
    YesDup[Redirect to Login]:::green
    NoDup[Approve New Registration]:::purple
    Generate2["Generate Tracking Number<br/>Generate PIN"]:::purple
    Dashboard2[Resident Dashboard]:::gray

    Start --> RegForm
    RegForm --> DupCheck

    DupCheck -->|No Match| NoMatch
    NoMatch --> CreateAcc
    CreateAcc --> Generate
    Generate --> Dashboard

    DupCheck -->|Match Found| Match
    Match --> ExistingMsg
    ExistingMsg --> LoginOpt
    LoginOpt -.-> Insist
    Insist --> StaffVerif
    StaffVerif --> StaffReview
    StaffReview --> Confirmed

    Confirmed -->|Yes| YesDup
    Confirmed -->|No| NoDup
    NoDup --> Generate2
    Generate2 --> Dashboard2
    YesDup --> Dashboard2

    classDef gray fill:#e0e0e0,stroke:#888,stroke-width:2,color:#333,rx:8
    classDef purple fill:#d4a5f5,stroke:#8b3dbf,stroke-width:2,color:#2d1b4e,rx:8
    classDef green fill:#a8e6cf,stroke:#4a9e6e,stroke-width:2,color:#1a3b2a,rx:8
    classDef orange fill:#ffd699,stroke:#e67e22,stroke-width:2,color:#4a2800,rx:8
```

**Figure 2.** Registration process with duplicate account detection. The system checks for existing records using First Name, Last Name, and Date of Birth before creating a new account. If a match is found, the resident is guided to log in or contact Barangay Staff for verification. Staff can override the duplicate if it is a false positive.

### Validator Considerations

1. **Duplicate Detection Criteria:** The primary matching fields are **First Name**, **Last Name**, and **Date of Birth**. An additional field such as **Barangay/Purok** may be used as a tie-breaker to reduce false matches.

2. **Staff Override:** If a duplicate is detected but the resident claims they do not already have an account, Barangay Staff can review the records, verify identity, and approve a legitimate new registration if the records do not actually belong to the same person.
