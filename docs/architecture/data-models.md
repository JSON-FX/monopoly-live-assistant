# Data Models
* **User**
    * **Purpose:** To store user account information for authentication and to associate sessions with a specific user.
    * **TypeScript Interface:**
        ```typescript
        export interface User {
          id: number;
          name: string;
          email: string;
        }
        ```
* **Session**
    * **Purpose:** To store all the data for a single, complete gameplay session.
    * **TypeScript Interface:**
        ```typescript
        export interface Session {
          id: number;
          userId: number;
          startTime: string;
          endTime: string | null;
          // ... and other fields
        }
        ```
* **Spin**
    * **Purpose:** To record every individual spin result and its financial outcome within a session.
    * **TypeScript Interface:**
        ```typescript
        export interface Spin {
          id: number;
          sessionId: number;
          result: string;
          betAmount: number;
          pl: number; // Profit or Loss
        }
        ``` 