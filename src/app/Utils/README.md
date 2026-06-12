# 🛠 Utilities

Helper classes that provide specialized functionality across the application.

## 🛡 `SvgSanitizer.php`
- **Class**: `SvgSanitizer`
    - **Purpose**: Essential for the CMS features that allow uploading raw SVG code (for platform icons). This class provides robust **XSS prevention** by meticulously cleaning SVG markup.
- **Methods**:
    - `sanitize(string $svg): string`:
        - **Purpose**: Takes a raw SVG string and removes potentially malicious content.
        - **Logic**:
            1.  **DOM Parsing**: Uses `DOMDocument` to parse the SVG string. It wraps the SVG in a temporary `<div>` to handle cases where the input might be an SVG fragment rather than a complete document. `libxml_use_internal_errors(true)` is used to suppress warnings for malformed XML, which is common with user-provided SVG.
            2.  **XPath Querying**: Employs `DOMXPath` to query and manipulate elements and attributes within the SVG structure. It registers the `xlink` namespace to correctly handle `xlink:href` attributes.
            3.  **Script Removal**: Iterates through and removes all `<script>` elements, which are a primary vector for XSS attacks.
            4.  **Event Handler Stripping**: Queries for all attributes that start with `on` (e.g., `onclick`, `onload`, `onerror`) and removes them. These attributes can execute JavaScript when triggered.
            5.  **Dangerous Protocol Removal**: Checks `href`, `xlink:href`, and `src` attributes for `javascript:` pseudo-protocols and removes them, preventing malicious script execution through links or embedded resources.
            6.  **Error Cleanup**: `libxml_clear_errors()` is called to clean up any parsing errors that occurred.
            7.  **Output**: Returns the cleaned SVG string.

## 🔐 `TOTPAuthenticator.php`
- **Class**: `TOTPAuthenticator`
    - **Purpose**: A native implementation of the Time-based One-Time Password (TOTP) algorithm, as defined in RFC 6238. This class is used for Two-Factor Authentication (2FA) in the admin panel.
- **Attributes**:
    - `protected int $_codeLength = 6`: Defines the length of the generated OTP code (defaulting to 6 digits).
- **Methods**:
    - `createSecret(int $secretLength = 16): string`:
        - **Purpose**: Generates a random Base32 secret key, which is the foundation for TOTP.
        - **Logic**: Creates a string of specified `$secretLength` (default 16) using a set of valid Base32 characters (A-Z, 2-7). `random_int()` is used for cryptographically secure random number generation.
    - `getCode(string $secret, ?int $timeSlice = null): string`:
        - **Purpose**: Calculates the current TOTP code for a given secret and time slice.
        - **Logic**:
            1.  **Time Slice**: Determines the current 30-second time window (`floor(time() / 30)`).
            2.  **Base32 Decode**: Decodes the Base32 `$secret` into its binary form using the private `_base32Decode` method.
            3.  **HMAC-SHA1**: Computes an HMAC-SHA1 hash using the binary secret as the key and the time slice (packed into a binary string) as the message.
            4.  **Dynamic Truncation**: Extracts a 4-byte dynamic truncation value from the HMAC result based on the last nibble of the HMAC.
            5.  **OTP Generation**: Converts the truncated hash to an integer, applies a modulo operation based on `$_codeLength`, and then zero-pads the result to ensure it's a 6-digit string.
    - `verifyCode(string $secret, string $code, int $discrepancy = 1, ?int $currentTimeSlice = null): bool`:
        - **Purpose**: Verifies if a user-provided code is valid for a given secret, allowing for a small time discrepancy.
        - **Logic**:
            1.  **Time Window**: Calculates the current time slice and then checks codes for a window of `discrepancy` time slices before and after the current one (e.g., `current - 1`, `current`, `current + 1`). This accounts for clock drift between the server and the authenticator app.
            2.  **Code Comparison**: For each time slice in the window, it generates a TOTP code using `getCode()` and compares it to the user-provided `$code`.
            3.  **Return**: Returns `true` if a match is found within the allowed discrepancy, `false` otherwise.
    - `getOTPAuthUrl(string $name, string $secret, ?string $title = null): string`:
        - **Purpose**: Generates a standard `otpauth://` URI, which can be used to create QR codes for easy setup in authenticator apps.
        - **Logic**: Constructs the URI using the provided account `$name`, `$secret`, and issuer `$title`, ensuring all components are URL-encoded for safety.
    - `_base32Decode(string $secret): string`:
        - **Purpose**: A private helper method to decode a Base32 encoded string into its binary representation.
        - **Logic**: Iterates through the Base32 string, mapping each character to its 5-bit value. These 5-bit chunks are then combined and reassembled into 8-bit bytes.

## 📜 Usage
These classes are stateless and typically contain static methods or are instantiated as needed by Controllers.