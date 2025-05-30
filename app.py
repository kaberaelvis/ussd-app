from flask import Flask, request
import pymysql
import json
import os

# Define base directory
base_dir = os.path.dirname(os.path.abspath(__file__))

app = Flask(__name__)

# DB Connection
def get_db_connection():
    return pymysql.connect(host='localhost', user='root', password='', database='ines_ruhengeri')

@app.route('/ussd', methods=['POST'])
def ussd():
    session_id = request.form.get("sessionId", "")
    service_code = request.form.get("serviceCode", "")
    phone_number = request.form.get("phoneNumber", "")
    text = request.form.get("text", "")

    inputs = text.split("*") if text else []
    level = len(inputs)

    conn = get_db_connection()
    cursor = conn.cursor(pymysql.cursors.DictCursor)

    if level == 0:
        response = "CON 1. Enter your Registration Number"
    elif level == 1:
        response = "CON 2. Enter your Password"
    elif level == 2:
        reg_no = inputs[0]
        password = inputs[1]
        cursor.execute("SELECT * FROM students WHERE Registration_number=%s AND Password=%s", (reg_no, password))
        student = cursor.fetchone()

        if not student:
            response = "END Incorrect Registration Number or Password."
        else:
            cursor.execute("SELECT * FROM department")
            departments = cursor.fetchall()
            dept_map = {i+1: dept['Department_id'] for i, dept in enumerate(departments)}
            session_data = {"regNo": reg_no, "deptMap": dept_map}
            with open(f"{base_dir}/session/{session_id}.json", "w") as f:
                json.dump(session_data, f)

            menu = "CON 3. Choose Your Department\n" + "\n".join([f"{i+1}. {dept['Department_name']}" for i, dept in enumerate(departments)])
            response = menu
    elif level == 3:
        with open(f"{base_dir}/session/{session_id}.json") as f:
            session_data = json.load(f)

        reg_no = session_data["regNo"]
        dept_map = session_data["deptMap"]
        selected = inputs[2]
        dept_id = dept_map.get(int(selected))

        cursor.execute("SELECT Department_id FROM students WHERE Registration_number=%s", (reg_no,))
        student_dept = cursor.fetchone()["Department_id"]
        if dept_id != student_dept:
            response = "END You don't belong in this department."
        else:
            cursor.execute("SELECT * FROM courses WHERE Department_id=%s", (dept_id,))
            courses = cursor.fetchall()
            course_map = {i+1: course["Course_id"] for i, course in enumerate(courses)}
            course_names = {i+1: course["course_name"] for i, course in enumerate(courses)}

            session_data.update({"deptId": dept_id, "courseMap": course_map, "courseNames": course_names})
            with open(f"{base_dir}/session/{session_id}.json", "w") as f:
                json.dump(session_data, f)

            menu = "CON 4. Choose Your Course\n" + "\n".join([f"{i+1}. {course['course_name']}" for i, course in enumerate(courses)])
            response = menu
    elif level == 4:
        with open(f"{base_dir}/session/{session_id}.json") as f:
            session_data = json.load(f)

        reg_no = session_data["regNo"]
        course_map = session_data["courseMap"]
        course_names = session_data["courseNames"]
        selected_course = inputs[3]
        course_id = course_map.get(int(selected_course))
        course_name = course_names.get(int(selected_course), "Unknown Course")

        cursor.execute("SELECT Student_name FROM students WHERE Registration_number=%s", (reg_no,))
        student = cursor.fetchone()
        cursor.execute("SELECT * FROM marks WHERE Registration_number=%s AND Course_id=%s", (reg_no, course_id))
        marks = cursor.fetchone()

        if not marks:
            response = "END No results found for selected course."
        else:
            status = "Passed" if marks["Final_marks"] > 10 else "Failed"
            response = f"END Hello {student['Student_name']} in {course_name},\n"                        f"You have received:\n"                        f"- Quiz: {marks['Quiz']}\n"                        f"- CAT: {marks['CAT']}\n"                        f"- Final Exam: {marks['Final_exam']}\n"                        f"- Grand Total: {marks['Grand_Total']}\n"                        f"- Final Marks: {marks['Final_marks']}\n"                        f"You have: {status}"
    else:
        response = "END Invalid input. Please try again."

    conn.close()
    return response

if __name__ == "__main__":
    app.run(port=5000)