### **Fullstack Architecture Document: Monopoly Live Betting Assistant**
*Version: 1.0*
*Date: 2025-07-27*

#### **Section 1: Introduction**
This document outlines the complete fullstack architecture for the Monopoly Live Betting Assistant, including backend systems, frontend implementation, and their integration. It serves as the single source of truth for AI-driven development, ensuring consistency across the entire technology stack. This unified approach combines what would traditionally be separate backend and frontend architecture documents, streamlining the development process.

* **Starter Template or Existing Project**
    * **N/A - Greenfield project.** No existing starter template was specified in the PRD. The architecture will be designed from scratch based on the specified technology stack (Laravel, React) and the monorepo structure assumed in the PRD, allowing for a solution custom-tailored to our specific requirements.

* **Change Log**
| Date | Version | Description | Author |
| :--- | :--- | :--- | :--- |
| 2025-07-27 | 1.0 | Initial Architecture Draft | Winston (Architect) |

---

#### **Section 2: High Level Architecture (Revised)**
* **Platform and Infrastructure Choice**
    * **Platform Recommendation:** **Docker with Docker Compose**
    * **Key Services (as Docker containers):**
        * **Backend:** A PHP container running the Laravel application.
        * **Frontend:** A Node.js container for running the React development server.
        * **Database:** A MySQL container.
    * **Rationale:** This approach is the industry standard for local development. It creates a consistent, isolated, and easy-to-manage environment for all parts of our application (backend, frontend, database) that can run on a single machine on your local network. Most importantly, this setup is **highly portable**. When you decide to host the application online in the future, we can deploy these same application containers to any cloud provider with minimal changes.

* **High Level Architecture Diagram (Revised for Local Deployment)**
    ```mermaid
    graph TD
        subgraph Local Machine / Personal Server
            Browser
            subgraph Docker Environment
                React[React Dev Server Container]
                Laravel[Laravel API Container]
                MySQL[MySQL Database Container]
            end
        end

        Browser --> React;
        React --> Laravel;
        Laravel --> MySQL;
    ```

* **Architectural Patterns**
    * **Serverless Architecture:** (Future Goal) While deployed locally via Docker, the application will be designed with serverless principles in mind (e.g., stateless API) to facilitate future migration to a platform like AWS Lambda.
    * **Component-Based UI:** Leveraging React and Shadcn UI to build the frontend from reusable, isolated components.
    * **Repository Pattern (Backend):** The Laravel backend will use the Repository Pattern to abstract the data access logic, decoupling the core business logic from the MySQL database.

---

#### **Section 3: Tech Stack**
| Category | Technology | Version | Purpose | Rationale |
| :--- | :--- | :--- | :--- | :--- |
| Frontend Language | TypeScript | Latest | Language for React | Provides type safety and scalability. |
| Frontend Framework | React | Latest | Core UI framework | Modern, component-based, and as requested. |
| UI Component Library| Shadcn UI | Latest | Pre-built UI components | Speeds up development with accessible, stylish components. |
| State Management | React Context/Zustand | Latest | Manages UI state | Start simple with built-in tools, can scale if needed. |
| Backend Framework | Laravel | Latest | Core backend framework| Robust, full-featured PHP framework as requested. |
| API Style | REST API | N/A | Frontend-Backend Communication | Standard, well-understood, and native to Laravel. |
| Database | MySQL | 8.0+ | Data persistence | Reliable, widely-used relational database as requested. |
| Authentication | Laravel Sanctum | Latest | SPA Authentication | Lightweight, official Laravel package for API authentication. |
| Frontend Testing | Vitest | Latest | Unit & Component Testing | Modern, fast, and compatible with the Vite build tool. |
| Backend Testing | PHPUnit | Latest | Unit & Integration Testing | The default, robust testing framework for Laravel. |
| IaC Tool | Docker Compose | Latest | Local Environment Orchestration | Defines and manages our multi-container local setup. |
| Build Tool | Vite | Latest | Frontend Asset Bundling | The modern standard for building React applications. |
| CSS Framework | Tailwind CSS | Latest | Utility-First CSS | A dependency of Shadcn UI, provides styling utilities. |

---

#### **Section 4: Data Models**
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

---

#### **Section 5: API Specification**
* **REST API Specification (OpenAPI 3.0)**
    * A foundational OpenAPI specification defines the key endpoints, such as `POST /api/sessions` to start a session and `POST /api/sessions/{id}/spins` to add a spin, ensuring a clear contract between the frontend and backend.

---

#### **Section 6: Components**
* **Component List & Diagram**
    * The system is logically broken down into a `Frontend (React SPA)`, a `Backend API (Laravel)` which contains an `Auth Service` and a `Session Service`, `Repositories` for data access, and the `Database`. This separation of concerns is key to a maintainable system.

---

#### **Section 7: External APIs**
* Not applicable for the MVP. The application is self-contained.

---

#### **Section 8: Core Workflows**
* **Workflow: Adding a Spin to a Session**
    * A sequence diagram details the interactions from a user's click in the `React SPA`, through the `Laravel API` and `SessionService`, to the `Repositories` and finally the `MySQL Database`, illustrating the end-to-end data flow for the core application loop.

---

#### **Section 9: Database Schema**
* The document contains the complete SQL DDL (`CREATE TABLE ...`) for the `users`, `sessions`, and `spins` tables, including primary keys, foreign keys, and appropriate data types to enforce data integrity.

---

#### **Section 10: Frontend Architecture**
* This section defines a feature-based directory structure for the React application, provides a standard component template, and outlines patterns for state management (Global, Local, Server Cache), routing (`React Router`), and a dedicated API services layer.

---

#### **Section 11: Backend Architecture**
* This section details the backend structure, including API route definitions in `routes/api.php`, lean controller templates that delegate logic to services, the implementation of the Repository Pattern for the data access layer, and the use of `auth:sanctum` middleware for security.

---

#### **Section 12: Unified Project Structure**
* A detailed ASCII tree diagram illustrates the complete monorepo structure, showing the placement of the `backend` (Laravel) and `frontend` (React) applications within a `packages` directory, and a `shared` directory for common TypeScript types.

---

#### **Section 13: Development Workflow**
* This section provides a complete guide to setting up the local development environment, including prerequisites (Docker, Node, Composer), initial setup commands, and a template for the necessary environment variables.

---

#### **Section 14: Deployment Architecture**
* The deployment strategy for the MVP is defined as local-only via `docker-compose`. A clear, portable strategy for future cloud deployment is also outlined. A conceptual CI/CD pipeline using GitHub Actions is included to automate testing.

---

#### **Section 15: Security and Performance**
* This section establishes a baseline of security best practices (XSS prevention, input validation, secure password hashing) and performance goals (sub-200ms API response, sub-500KB JS bundle) for the application.

---

#### **Section 16: Testing Strategy**
* A multi-layered approach following the testing pyramid is defined. The document specifies the organization for unit, integration, and E2E tests and provides code examples for each type (Vitest, PHPUnit, Playwright).

---

#### **Section 17: Coding Standards**
* A set of mandatory, critical rules for development is established, focusing on type sharing, service layer encapsulation, and the repository pattern. Standard naming conventions for components, routes, and database tables are also defined.