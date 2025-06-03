from flask import Flask, request
import mysql.connector
import os

app = Flask(__name__)

def get_db_connection():
    return mysql.connector.connect(
        host='localhost',
        user='root',
        password='',
        database='ines_ruhengeri'
    )

@app.route('/', methods=['POST'])
def ussd():
    session_id = request.form.get('sessionId')
    phone_number = request.form.get('phoneNumber')
    service_code = request.form.get('serviceCode')
    text = request.form.get('text') or ''

    print("Received USSD:", request.form.to_dict())  # ✅ Useful for debugging

    text_array = text.split('*')
    level = len(text_array)

    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    if level == 1 and text_array[0] == '':
        return "CON Enter your Registration Number:", 200, {'Content-Type': 'text/plain'}

    elif level == 1:
        reg_number = text_array[0]
        cursor.execute("SELECT * FROM students WHERE Registration_number = %s", (reg_number,))
        student = cursor.fetchone()

        if student:
            return "CON Enter your password:", 200, {'Content-Type': 'text/plain'}
        else:
            return "END Incorrect Registration Number", 200, {'Content-Type': 'text/plain'}

    else:
        return "END Something went wrong", 200, {'Content-Type': 'text/plain'}

# ✅ This starts the Flask server
if __name__ == '__main__':
    app.run(debug=True, port=5050)
