-- Create students table
CREATE TABLE IF NOT EXISTS students (
    Student_id INT PRIMARY KEY AUTO_INCREMENT,
    Registration_number VARCHAR(50) UNIQUE NOT NULL,
    Student_name VARCHAR(100) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Department_id INT NOT NULL,
    Created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create department table
CREATE TABLE IF NOT EXISTS department (
    Department_id INT PRIMARY KEY AUTO_INCREMENT,
    Department_name VARCHAR(100) NOT NULL,
    Created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    Course_id INT PRIMARY KEY AUTO_INCREMENT,
    Course_name VARCHAR(100) NOT NULL,
    Department_id INT NOT NULL,
    Created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Department_id) REFERENCES department(Department_id)
);

-- Create marks table
CREATE TABLE IF NOT EXISTS marks (
    Mark_id INT PRIMARY KEY AUTO_INCREMENT,
    Registration_number VARCHAR(50) NOT NULL,
    Course_id INT NOT NULL,
    Quiz DECIMAL(5,2) NOT NULL,
    CAT DECIMAL(5,2) NOT NULL,
    Final_exam DECIMAL(5,2) NOT NULL,
    Created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Registration_number) REFERENCES students(Registration_number),
    FOREIGN KEY (Course_id) REFERENCES courses(Course_id)
);

-- Insert sample departments
INSERT INTO department (Department_name) VALUES
('Computer Science'),
('Business Administration'),
('Engineering'),
('Health Sciences');

-- Insert sample courses
INSERT INTO courses (Course_name, Department_id) VALUES
('Introduction to Programming', 1),
('Database Systems', 1),
('Management Principles', 2),
('Accounting Fundamentals', 2);

-- Insert sample student
INSERT INTO students (Registration_number, Student_name, Password, Department_id) VALUES
('2023/CS/001', 'John Doe', 'password123', 1);

-- Insert sample marks
INSERT INTO marks (Registration_number, Course_id, Quiz, CAT, Final_exam) VALUES
('2023/CS/001', 1, 15.00, 20.00, 35.00);
