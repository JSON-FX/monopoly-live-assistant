-- MySQL initialization script for development environment
-- This script runs when the MySQL container starts for the first time

-- Create application database
CREATE DATABASE IF NOT EXISTS monopoly_live;

-- Remove existing users to start clean
DROP USER IF EXISTS 'monopoly'@'%';
DROP USER IF EXISTS 'monopoly'@'localhost';
DROP USER IF EXISTS 'root'@'%';

-- Create application user with proper permissions for all hosts
CREATE USER 'monopoly'@'%' IDENTIFIED WITH mysql_native_password BY 'password';
CREATE USER 'monopoly'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
GRANT ALL PRIVILEGES ON monopoly_live.* TO 'monopoly'@'%';
GRANT ALL PRIVILEGES ON monopoly_live.* TO 'monopoly'@'localhost';

-- Create root user for external connections
CREATE USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'password';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;

-- Update existing root@localhost user
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;

-- Flush privileges to apply changes
FLUSH PRIVILEGES; 