# Requirements
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