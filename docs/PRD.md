### **Product Requirements Document: Monopoly Live Betting Assistant**
*Version: 1.0*
*Date: 2025-07-27*

#### **Section 1: Goals and Background Context**
* **Goals**
    * To provide the user with real-time, strategy-driven "Bet" or "Skip" signals to eliminate guesswork.
    * To ensure 100% accurate execution of the selected betting strategy (initially Martingale) by automating tracking and calculations.
    * To reduce gameplay stress and errors associated with manual tracking in a fast-paced environment.
    * To deliver a robust, maintainable, and scalable application by adhering to strict SOLID principles.
* **Background Context**
    Strategic players of Monopoly Live currently face a significant challenge in manually tracking spin history and applying complex betting strategies in real-time. This manual process is stressful and prone to errors, leading to flawed strategy execution and a diminished gameplay experience. This application will solve this by providing a purpose-built tool that offloads all tracking and calculation, allowing the player to focus on the game while executing their strategy with discipline and precision.
* **Change Log**
| Date | Version | Description | Author |
| :--- | :--- | :--- | :--- |
| 2025-07-27 | 1.0 | Initial PRD Draft from Project Brief | John (PM) |

---

#### **Section 2: Requirements**
* **Functional**
    1.  **FR1:** The system shall require users to register and log in before creating or viewing sessions.
    2.  **FR2:** The system shall allow a logged-in user to start a new session, end a current session, and resume an interrupted session.
    3.  **FR3:** The system shall provide a real-time interface for the user to input spin results, including an undo function to correct the most recent entry.
    4.  **FR4:** The system shall implement the "'Bet on 1' Mode" with the "Martingale" strategy, processing the last three spin results to produce a "Bet" or "Skip" signal.
    5.  **FR5:** The system shall accurately calculate and display the session's Profit/Loss and Win Rate, correctly implementing all defined logic for numeric, bonus, and "Chance" segment outcomes.
    6.  **FR6:** The system shall save all session data to the database and provide a history page where the user can view their past sessions and export the data.
    7.  **FR7:** The system shall display visual feedback for winning streaks and a progressive, color-coded warning for losing streaks.
    8.  **FR8:** The system shall automatically enforce a session end after 6 consecutive losses when in the default mode and strategy.
* **Non-Functional**
    1.  **NFR1:** The application UI must have near-instantaneous response times for data entry to be usable during a live game.
    2.  **NFR2:** The codebase must strictly adhere to SOLID principles and a component-based architecture to ensure long-term maintainability.
    3.  **NFR3:** The system must be built using Laravel (Latest), React (Latest), Shadcn UI, and MySQL.
    4.  **NFR4:** The user interface must be intuitive, with clear and unambiguous status indicators and visual warnings.
    5.  **NFR5:** All session data, particularly P/L and spin history, must be stored with high integrity and be permanently associated with the correct user account.

---

#### **Section 3: User Interface Design Goals**
* **Overall UX Vision**
    The user experience will be that of a clean, minimalist, and highly functional "heads-up display" for the player. The interface should present critical, real-time information at a glance, acting as a professional tool that aids decision-making without distracting from the main game on another screen. The design will prioritize clarity, speed, and accuracy above all else.
* **Key Interaction Paradigms**
    * **Real-time Data Entry:** The primary interaction is the rapid tapping of segment buttons to log game results instantly.
    * **Status-driven Display:** The UI will be dominated by a large, clear, color-coded status card ("Bet"/"Skip") that commands the user's immediate attention.
    * **Single-Purpose Views:** The application will have a clear separation of concerns, with a "Live Session" page for active gameplay and a "History" page for post-game review.
* **Core Screens and Views**
    * Login Screen
    * User Registration Screen
    * Live Session Page
    * History Page
* **Accessibility**
    * **WCAG AA:** The application will be designed to meet WCAG 2.1 AA standards, ensuring it is usable for people with disabilities.
* **Branding**
    * A clean, modern, and data-focused aesthetic, leveraging the minimalist style of Shadcn UI. The color palette will be functional, using the defined colors (gray for '1', green for '2', etc.) to convey information about game segments and the progressive red/yellow/green system to communicate risk levels.
* **Target Device and Platforms**
    * **Web Responsive:** The application will be a responsive web app, optimized for use on both desktop and mobile browsers.

---

#### **Section 4: Technical Assumptions**
* **Repository Structure: Monorepo**
    * The project will be structured as a monorepo, containing both the Laravel backend and React frontend in a single repository. This approach will simplify dependency management and facilitate code sharing (e.g., for data types) between the two applications.
* **Service Architecture: Monolith**
    * The Laravel backend will be built as a single, monolithic service. This is the most straightforward and efficient architecture for the MVP's defined scope, simplifying development, testing, and deployment.
* **Testing Requirements: Unit + Integration**
    * The project will require both unit tests for individual components/classes and integration tests to verify interactions between different parts of the application (e.g., API endpoints connecting to the database).
* **Additional Technical Assumptions and Requests**
    * As previously defined, the strict implementation of SOLID principles and a component-based architecture is a mandatory assumption for all development work.

---

#### **Section 5: Epic List**
1.  **Epic 1: Foundation & User Management**
    * **Goal:** To establish the core project infrastructure and implement a complete user authentication system.
2.  **Epic 2: Core Session & Betting Logic Engine**
    * **Goal:** To build the fundamental engine for session management, P/L tracking, and executing the "Bet on 1" Martingale strategy.
3.  **Epic 3: Live Gameplay UI & Real-time Feedback**
    * **Goal:** To develop the full, card-based user interface for live gameplay, including real-time data input and all visual user feedback systems.
4.  **Epic 4: Session History & Data Export**
    * **Goal:** To create the "History" page, allowing users to review all their past sessions and export the data.

---

#### **Section 6: Epic 1 - Foundation & User Management**
* **Expanded Goal:** To establish the project's technical foundation by setting up the monorepo, installing dependencies, and configuring the database. This epic will also deliver a complete, secure user authentication system, which is a prerequisite for all other application features.
* **Stories:**
    * **1.1: Initial Project Setup**: As a Developer, I want a new Laravel and React Monorepo project to be set up, so that I have a clean and consistent foundation for development.
    * **1.2: User Data Model**: As a Developer, I want a User model and database migration created, so that user information can be persisted in the database.
    * **1.3: User Registration**: As a new User, I want to register for an account, so that I can access the application's features.
    * **1.4: User Login and Logout**: As a registered User, I want to be able to log in and log out, so that I can securely access and manage my sessions.
    * **1.5: Route Protection**: As a Developer, I want to protect application routes, so that only authenticated users can access session-related pages.

---

#### **Section 7: Epic 2 - Core Session & Betting Logic Engine**
* **Expanded Goal:** To build and test the fundamental backend engine for creating and managing gameplay sessions. This includes implementing the complete P/L tracking rules and the "Bet on 1" Martingale strategy logic, and exposing it all via a secure API. This epic assumes a user is already authenticated.
* **Stories:**
    * **2.1: Session Data Models and Relationships**: As a Developer, I want `Session` and `Spin` data models with database migrations, so that I can store all data related to a user's gameplay session.
    * **2.2: Session Creation**: As a User, I want to start a new session, so that I can begin tracking a new game.
    * **2.3: Core P/L & Strategy Engine**: As a Developer, I want a backend service that processes a session's spin history, so that it can accurately calculate P/L and determine the next "Bet/Skip" status.
    * **2.4: Session Management API**: As a Frontend Developer, I want secure API endpoints to manage a session, so that the UI can display and update the game state.

---

#### **Section 8: Epic 3 - Live Gameplay UI & Real-time Feedback**
* **Expanded Goal:** To develop the complete, card-based user interface on the "Live Session" page. This epic will consume the API from Epic 2 to display session data, handle real-time spin input, and provide all the dynamic visual feedback required for live gameplay.
* **Stories:**
    * **3.1: Live Session Page Layout and Dashboard Card**: As a User, I want to see a clean, card-based layout on the Live Session page, so that I can easily view all critical session information.
    * **3.2: Status and Input Cards**: As a User, I want to see my current betting status and have clear buttons to input spin results, so that I can play the game in real-time.
    * **3.3: Live Spin History Card**: As a User, I want to see the history of spins for my current session in real-time, so that I have context for the app's decisions.
    * **3.4: Implementing Data Input Flow**: As a User, I want to click a segment button to record a spin result and see my entire dashboard update instantly, so that I can proceed to the next bet.
    * **3.5: Real-time Feedback System**: As a User, I want to receive clear visual feedback about my performance, so that I am aware of winning or losing streaks.

---

#### **Section 9: Epic 4 - Session History & Data Export**
* **Expanded Goal:** To deliver the long-term analytical value of the application by creating a dedicated "History" page. This will allow the user to view, analyze, and export data from all their previously completed gameplay sessions.
* **Stories:**
    * **4.1: API for Session History**: As a Frontend Developer, I want an API endpoint that provides a list of all my past sessions, so that I can display them on the History page.
    * **4.2: History Page UI**: As a User, I want to see a list of all my past gameplay sessions, so that I can track my performance over time.
    * **4.3: Session Detail View**: As a User, I want to click on a session from the History page to see its detailed spin-by-spin results, so that I can analyze a specific game.
    * **4.4: Data Export Functionality**: As a User, I want to export the data from a specific session, so that I can perform my own analysis in other tools.