-- ============================================
-- FITNESS APP DATABASE SCHEMA
-- MySQL Version
-- ============================================

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    age INT,
    gender ENUM('M', 'F', 'Other'),
    goal VARCHAR(255),
    experience_level ENUM('Beginner', 'Intermediate', 'Advanced'),
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Training Plans Table
CREATE TABLE IF NOT EXISTS training_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    template_type ENUM('PPL', 'UL', 'Full Body', 'Push/Pull/Legs', 'Custom'),
    duration_weeks INT DEFAULT 4,
    status ENUM('Active', 'Inactive', 'Archived') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- Workout Sessions Table
CREATE TABLE IF NOT EXISTS workout_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    training_plan_id INT,
    workout_date DATE NOT NULL,
    session_name VARCHAR(255),
    duration_minutes INT,
    energy_level INT COMMENT '1-10 scale',
    notes TEXT,
    completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (training_plan_id) REFERENCES training_plans(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_workout_date (workout_date)
);

-- Exercise Logs Table (Individual sets/reps)
CREATE TABLE IF NOT EXISTS exercise_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    workout_session_id INT NOT NULL,
    exercise_name VARCHAR(255) NOT NULL,
    set_number INT,
    reps INT,
    weight DECIMAL(8,2),
    rir INT COMMENT 'Reps in Reserve (0-5)',
    weight_unit ENUM('kg', 'lbs') DEFAULT 'kg',
    duration_seconds INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workout_session_id) REFERENCES workout_sessions(id) ON DELETE CASCADE,
    INDEX idx_session_id (workout_session_id)
);

-- Nutrition Logs Table
CREATE TABLE IF NOT EXISTS nutrition_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    log_date DATE NOT NULL,
    meal_type ENUM('Breakfast', 'Lunch', 'Dinner', 'Snack', 'Post-Workout') NOT NULL,
    food_item VARCHAR(255) NOT NULL,
    quantity DECIMAL(8,2),
    unit VARCHAR(50),
    calories INT,
    protein DECIMAL(8,2),
    carbs DECIMAL(8,2),
    fats DECIMAL(8,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, log_date)
);

-- User Measurements Table
CREATE TABLE IF NOT EXISTS user_measurements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    measurement_date DATE NOT NULL,
    weight DECIMAL(8,2),
    weight_unit ENUM('kg', 'lbs') DEFAULT 'kg',
    chest DECIMAL(8,2),
    waist DECIMAL(8,2),
    hips DECIMAL(8,2),
    biceps DECIMAL(8,2),
    thighs DECIMAL(8,2),
    body_fat_percentage DECIMAL(5,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, measurement_date),
    INDEX idx_user_id (user_id)
);

-- Progress Photos Table
CREATE TABLE IF NOT EXISTS progress_photos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    photo_date DATE NOT NULL,
    photo_type ENUM('Front', 'Back', 'Side') NOT NULL,
    file_path VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- Create Indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_training_plans_user ON training_plans(user_id);
CREATE INDEX idx_workout_sessions_user ON workout_sessions(user_id);
CREATE INDEX idx_workout_sessions_date ON workout_sessions(workout_date);
CREATE INDEX idx_exercise_logs_session ON exercise_logs(workout_session_id);
CREATE INDEX idx_nutrition_logs_user_date ON nutrition_logs(user_id, log_date);
CREATE INDEX idx_measurements_user ON user_measurements(user_id);
