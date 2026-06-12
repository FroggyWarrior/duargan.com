# 🧠 Core Engine

This directory contains the foundational classes that power the custom MVC framework. These classes handle the application lifecycle, from routing the initial request to managing database connections.

## 📄 Core Classes

### 🛠 `Router.php`
- **Class**: `Router`
    - **Purpose**: Maps incoming URIs to specific Controller actions based on predefined routes and HTTP methods. It's responsible for parsing the URL, extracting parameters, and invoking the correct controller method.
- **Attributes**:
    - `protected array $routes`: An associative array that stores all registered routes. Routes are grouped by their HTTP method (e.g., 'GET', 'POST'), and each entry contains the route pattern, the controller class name, and the action method name.
- **Methods**:
    - `add(string $route, string $controller, string $action, string $method = 'GET')`:
        - **Purpose**: Registers a new route with the router.
        - **Logic**: Takes a `$route` pattern (which can include dynamic parameters like `{id}`), the `$controller` class name, the `$action` method name, and the HTTP `$method`. It stores this information in the `$routes` array, making it available for dispatching.
    - `dispatch(string $uri)`:
        - **Purpose**: Processes the incoming URI and dispatches the request to the appropriate controller action.
        - **Logic**:
            1.  Retrieves the current HTTP request method (`$_SERVER['REQUEST_METHOD']`) and parses the URI path.
            2.  Iterates through all routes registered for the current HTTP method.
            3.  For each registered route, it converts the route pattern (e.g., `/admin/songs/edit/{id}`) into a regular expression (e.g., `#^/admin/songs/edit/([a-zA-Z0-9_]+)$#`).
            4.  Uses `preg_match` to check if the incoming URI matches the generated regular expression.
            5.  If a match is found, it extracts any dynamic parameters from the URI (e.g., the `id` value).
            6.  It then constructs the fully qualified controller class name and checks if the class and its specified action method exist.
            7.  If valid, it instantiates the controller and calls the action method using `call_user_func_array`, passing the extracted URI parameters as arguments.
            8.  If no matching route is found after checking all possibilities, or if the controller/action is invalid, it responds with an HTTP 404 "Page Not Found" error.

### 🗄 `Database.php`
- **Class**: `Database`
    - **Purpose**: Implements the **Singleton Pattern** to manage database connections. It ensures that only one database connection instance exists per user role (read-only for public content, read-write for admin operations) throughout the application's lifecycle.
- **Attributes**:
    - `private static $instances = []`: A static array that holds the single instance of the `Database` class for each connection type (`content` or `admin`). This is central to the Singleton pattern.
    - `private $pdo`: Stores the active PDO (PHP Data Objects) connection instance.
- **Methods**:
    - `__construct(array $config)`:
        - **Purpose**: The private constructor prevents direct instantiation of the class, enforcing the Singleton pattern. It establishes the actual PDO database connection.
        - **Logic**: Takes a `$config` array containing database credentials (`host`, `port`, `dbname`, `user`, `password`). It attempts to create a new `PDO` object. Crucially, it sets several PDO attributes:
            - `PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION`: Configures PDO to throw exceptions on errors, making error handling more robust.
            - `PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC`: Sets the default fetch mode to return rows as associative arrays.
            - `PDO::ATTR_EMULATE_PREPARES, false`: **Disables emulated prepared statements**, forcing MySQL to use native prepared statements. This is a critical security measure against SQL injection attacks.
        - If the connection fails, it terminates the script with an error message.
    - `getInstance(string $type = 'content')`:
        - **Purpose**: The static entry point to get a `Database` instance. It ensures that only one instance per `$type` is created.
        - **Logic**: Checks if an instance for the given `$type` (e.g., 'content' or 'admin') already exists in `self::$instances`. If not, it calls `self::getConfig($type)` to retrieve the appropriate database credentials and then creates a new `Database` object, storing it in `self::$instances`. It then returns the stored instance.
    - `getConfig(string $type)`:
        - **Purpose**: A private static helper method to retrieve database connection parameters based on the requested connection `$type`.
        - **Logic**: Reads database host, port, and name from environment variables (`DB_HOST`, `DB_PORT`, `DB_NAME`), providing sensible defaults if they are not set. If `$type` is 'admin', it uses `WRITER_DB_USER` and `WRITER_DB_PASSWORD` from environment variables. Otherwise (for 'content'), it uses `READER_DB_USER` and `READER_DB_PASSWORD`. This implements the **Dual-User Architecture** for enhanced security.
    - `getConnection()`:
        - **Purpose**: Provides access to the underlying PDO connection object.
        - **Logic**: Simply returns the `$this->pdo` instance.

### 🎮 `Controller.php`
- **Class**: `Controller`
    - **Purpose**: This is an abstract base class that all other controllers in the system extend. It provides fundamental, reusable functionalities common to all controllers, such as rendering views and performing HTTP redirects.
- **Methods**:
    - `render(string $view, array $data = [])`:
        - **Purpose**: Loads and displays a specified view file, making an associative array of data available to that view.
        - **Logic**:
            1.  Uses `extract($data)` to convert the keys of the `$data` array into local variables within the current scope. This allows view files to directly access variables like `$pageTitle` instead of `$data['pageTitle']`.
            2.  Constructs the full file path to the view (e.g., `../Views/home.php`).
            3.  Checks if the view file exists. If it does, it includes the file, which then executes the PHP code within the view and outputs HTML.
            4.  If the view file is not found, it terminates script execution with an error message, preventing partial or broken page rendering.
    - `redirect(string $url)`:
        - **Purpose**: Performs an HTTP redirect, sending the user's browser to a different URL.
        - **Logic**:
            1.  Sets the `Location` HTTP header to the provided `$url`. This instructs the browser to navigate to the new URL.
            2.  Calls `exit` immediately after setting the header. This is crucial to prevent any further script execution or output after the redirect header has been sent, which could lead to unexpected behavior or security vulnerabilities.

### 🛠 `ViewHelper.php`
- **Class**: `ViewHelper`
    - **Purpose**: A static utility class designed to assist the presentation layer (Views) by providing methods for rendering common HTML snippets, particularly for dynamic content like social media icons.
- **Methods**:
    - `renderSocialMedia(array $platforms, string $class = 'social-links')`:
        - **Purpose**: Generates and returns the HTML markup for social media icons, ensuring secure rendering of SVG content.
        - **Logic**:
            1.  Accepts an array of `$platforms` (typically fetched from the database) and an optional CSS `$class` for the container.
            2.  If the `$platforms` array is empty, it returns an empty `<div>` with the specified class.
            3.  Iterates through each platform in the `$platforms` array:
                -   Constructs an `<a>` (anchor) HTML tag.
                -   Sets the `href` attribute to the platform's `base_url`, `aria-label` for accessibility, `target="_blank"` to open in a new tab, and `rel="noopener"` for security best practices (preventing tabnabbing).
                -   **Crucially**, it processes the `icon_svg` content: `html_entity_decode(stripslashes($platform['icon_svg']))`. This step is safe because the SVG content is already sanitized by `SvgSanitizer` during the upload/storage phase, ensuring no malicious scripts are present. `stripslashes` is used to reverse any escaping that might have occurred during database storage.
                -   Appends the processed SVG content inside the `<a>` tag.
            4.  Returns the complete HTML string for all social media links.

## 🔄 Data Flow
1. `index.php` initializes the `Router`.
2. `Router::dispatch()` matches the `$_SERVER['REQUEST_URI']`.
3. The matched `Controller` is instantiated.
4. The `Controller` interacts with the `Database` (typically indirectly via a `Model`).
5. The `Controller` calls `render()`, which pulls in the required file from the `Views` folder.