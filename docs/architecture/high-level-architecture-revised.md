# High Level Architecture (Revised)
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