# Academic Performance USSD Application

This USSD application allows students to check their academic performance through a multi-step USSD interface.

## Setup Instructions

1. **Database Setup**
   - Create a MySQL database named `ines_ruhengeri`
   - Create the following tables:
     - `students`: Stores student information
     - `department`: Stores department information
     - `courses`: Stores course information
     - `marks`: Stores student marks

2. **Configuration**
   - Edit `config.php` with your Africa's Talking credentials:
     ```php
     $AT_USERNAME = 'your_username';
     $AT_API_KEY = 'your_api_key';
     ```

3. **Web Server**
   - Ensure PHP is installed and configured
   - Place all files in your web server's directory
   - Make sure session directory is writable

4. **Africa's Talking Setup**
   - Create a USSD application on Africa's Talking
   - Set the webhook URL to point to your `ussd.php` endpoint
   - Test using Africa's Talking USSD simulator

## USSD Flow

1. Enter Registration Number
2. Enter Password
3. Choose Department
4. Choose Course
5. View Results

## Security Notes

- Passwords should be stored hashed in the database
- Input validation is implemented
- Session management is handled
- SQL injection prevention is implemented

## Error Handling

- Invalid registration numbers are handled
- Incorrect passwords are handled
- Invalid department/course selections are handled
- Database errors are handled gracefully

## Required Dependencies

- PHP 7.4 or higher
- MySQL/MariaDB
- Africa's Talking API credentials
- Web server (Apache/Nginx)
