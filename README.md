# Eclipse Shell Scanner

![Eclipse Shell Scanner](https://l.top4top.io/p_3611oacp51.png)

A lightweight yet powerful PHP-based malware scanner designed to clean up your server from malicious scripts, backdoors, and shells. Built with a modern Dark Mode UI that feels great to use—not clunky like those old-school scanners.

## Key Features

*   **Smart Scanning**: Uses heuristic methods to detect suspicious code patterns (eval, base64, obfuscated code, etc.).
*   **Live Action**: Found a dangerous file? You can **View**, **Edit**, or **Delete** it right there. No need to open your cPanel File Manager.
*   **Flexible Navigation**: Move around directories easily. Want to scan a specific folder or the root directory? Just type the path or use the navigation controls.

## How to Use

1.  Upload the `scanner.php` file to your hosting or server (e.g., inside `public_html` or your target folder).
2.  Access the file via your browser (e.g., `domain.com/scanner.php`).
3.  Set the directory you want to scan in the "Current Directory" field (defaults to the script's location).
4.  Click **Start Scan**.
5.  Wait for the process to finish. If you see red files, check their content. If it's malware, wipe it out (Delete/Edit).

## Disclaimer

This tool is made to help secure your server. **Use at Your Own Risk**. Always check the file before deleting it, as it might be a false positive system file. Don't forget to backup regularly!
