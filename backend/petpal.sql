CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    role ENUM('owner', 'sitter') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pet_owners (
  owner_id INT NOT NULL PRIMARY KEY,
  address VARCHAR(255) NOT NULL,
  profile_image VARCHAR(255),
  FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE pet_sitters (
  sitter_id INT NOT NULL PRIMARY KEY,
  experience_years INT,
  availability TEXT,
  bio TEXT,
  profile_image VARCHAR(255),
  FOREIGN KEY (sitter_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE pets (
    pet_id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    species ENUM('dog', 'cat') NOT NULL,
    breed VARCHAR(255),
    age INT,
    weight DECIMAL(5,2),
    health_notes TEXT,
    FOREIGN KEY (owner_id) REFERENCES pet_owners(owner_id) ON DELETE CASCADE
);

CREATE TABLE pet_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    image_url VARCHAR(255),
    favorite_toys VARCHAR(255),
    favorite_foods VARCHAR(255),
    personality TEXT,
    special_needs TEXT,
    custom_notes TEXT,
    FOREIGN KEY (pet_id) REFERENCES pets(pet_id) ON DELETE CASCADE
);

CREATE TABLE pet_notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_id INT NOT NULL,
    message TEXT NOT NULL,
    send_time DATETIME NOT NULL,
    status ENUM('unread', 'read') DEFAULT 'unread',
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (pet_id) REFERENCES pets(pet_id)
);
CREATE TABLE pet_habits (
  habit_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  pet_id INT NOT NULL,
  habit_type ENUM('feeding','potty','exercise','medication','sleep','custom') NOT NULL,
  habit_description TEXT,
  habit_time DATETIME NOT NULL,
  FOREIGN KEY (pet_id) REFERENCES pets(pet_id) ON DELETE CASCADE
);

CREATE TABLE schedules (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  pet_id INT,
  owner_id INT,
  task VARCHAR(255),
  scheduled_time DATETIME,
  repeat_frequency ENUM('none','daily','weekly','monthly'),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pet_id) REFERENCES pets(pet_id) ON DELETE CASCADE,
  FOREIGN KEY (owner_id) REFERENCES pet_owners(owner_id) ON DELETE CASCADE
);