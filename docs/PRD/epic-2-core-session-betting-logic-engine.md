# Epic 2 - Core Session & Betting Logic Engine
* **Expanded Goal:** To build and test the fundamental backend engine for creating and managing gameplay sessions. This includes implementing the complete P/L tracking rules and the "Bet on 1" Martingale strategy logic, and exposing it all via a secure API. This epic assumes a user is already authenticated.
* **Stories:**
    * **2.1: Session Data Models and Relationships**: As a Developer, I want `Session` and `Spin` data models with database migrations, so that I can store all data related to a user's gameplay session.
    * **2.2: Session Creation**: As a User, I want to start a new session, so that I can begin tracking a new game.
    * **2.3: Core P/L & Strategy Engine**: As a Developer, I want a backend service that processes a session's spin history, so that it can accurately calculate P/L and determine the next "Bet/Skip" status.
    * **2.4: Session Management API**: As a Frontend Developer, I want secure API endpoints to manage a session, so that the UI can display and update the game state. 