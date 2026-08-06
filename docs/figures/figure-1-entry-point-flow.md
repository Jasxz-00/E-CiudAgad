# Figure 1. Entry Point Decision Flow

```mermaid
flowchart TD
    Start([Start]):::gray
    Landing[Landing Page]:::gray
    NewRes[New Resident]:::purple
    Returning[Returning Resident]:::green
    RegProcess[Registration Process]:::purple
    LoginForm[Login]:::green
    Dashboard[Resident Dashboard]:::gray

    Start --> Landing
    Landing --> NewRes
    Landing --> Returning
    NewRes --> RegProcess
    Returning --> LoginForm
    RegProcess --> Dashboard
    LoginForm --> Dashboard

    classDef gray fill:#e0e0e0,stroke:#888,stroke-width:2,color:#333,rx:8
    classDef purple fill:#d4a5f5,stroke:#8b3dbf,stroke-width:2,color:#2d1b4e,rx:8
    classDef green fill:#a8e6cf,stroke:#4a9e6e,stroke-width:2,color:#1a3b2a,rx:8
```

**Figure 1.** Entry point decision flow showing the two paths a resident can take when accessing the E-Bilis Barangay Online Document Request and Processing Management System. New residents proceed through registration, while returning residents log in using their Tracking Number and PIN. Both paths converge at the Resident Dashboard.
