CREATE DATABASE secure_app;
USE secure_app;

CREATE TABLE users (
    id_number VARCHAR(20) PRIMARY KEY,
    first_name VARCHAR(50),
    middle_name VARCHAR(50),
    last_name VARCHAR(50),
    name_extension VARCHAR(10),
    birth_date DATE,
    age INT,
    address TEXT,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    auth_q1 VARCHAR(255),
    auth_a1 VARCHAR(255),
    auth_q2 VARCHAR(255),
    auth_a2 VARCHAR(255),
    auth_q3 VARCHAR(255),
    auth_a3 VARCHAR(255)
);