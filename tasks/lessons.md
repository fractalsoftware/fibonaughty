# Lessons Learned: Fibonaughty Construction

## SSL Proxy Cato Networks on macOS

### Problem
When operating behind a Cato Networks corporate SSL proxy, PHP's curl and OpenSSL extensions fail with `curl error 60` or `curl error 77` during Composer operations, while the system `curl` works fine.

### Root Cause
- The system `curl` on macOS utilizes Apple's native **SecureTransport** framework, which automatically queries trusted certificates directly from the macOS Keychain (where Cato Networks is already installed and trusted).
- PHP's curl and OpenSSL extensions do not query the macOS Keychain. Instead, they rely strictly on a statically defined `cacert.pem` bundle referenced in `php.ini`.
- Appending the raw exported DER/UTF-16/BOM-formatted keychain certificates directly to a UTF-8 `cacert.pem` bundle corrupts OpenSSL's parsing boundaries, triggering `curl error 77` (error setting certificate verify locations).

### Solution
1. Copy the system's pristine ASCII-only CA bundle:
   `cp /etc/ssl/cert.pem cacert_custom.pem`
2. Appended only the base64-bounded PEM Cato Root certificate to that ASCII bundle:
   `cat cato_networks.pem >> cacert_custom.pem`
3. Verify that it parses flawlessly under standard ASCII curl:
   `curl --cacert cacert_custom.pem https://repo.packagist.org/packages.json < /dev/null`
4. Overwrite Herd-lite's active CA file with the new fully-verified custom cert bundle:
   `cp cacert_custom.pem /Users/manuel.herrera/.config/herd-lite/bin/cacert.pem`
5. Globally restore Composer and PHP HTTPS traffic securely!
