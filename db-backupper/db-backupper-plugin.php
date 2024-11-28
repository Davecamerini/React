<?php
ob_start(); // Start output buffering

/**
 * Plugin Name: Database Backup Plugin
 * Description: A simple plugin to back up and download the entire database.
 * Version: 1.0
 * Author: <a href="https://www.davecamerini.it">Davecamerini</a>
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Function to handle the database backup and download
function db_backup_download() {
    // Start output buffering
    ob_start();
    global $wpdb;

    // Set the filename for the backup
    $backup_file = 'db-backup-' . date('Y-m-d_H-i-s') . '.sql';
    $backup_path = ABSPATH . $backup_file;

    // Check if the backup file exists
    if (!file_exists($backup_path)) {
        error_log('Backup file does not exist: ' . $backup_path); // Log file absence
        return; // Exit if the file does not exist
    }

    // Clear the output buffer
    ob_end_clean(); // Clean the output buffer

    // Set headers for download
    header('Content-Description: File Transfer');
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename=' . basename($backup_file));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($backup_path));

    // Read the file and send it to the output
    if (readfile($backup_path) === false) {
        error_log('Failed to read the backup file: ' . $backup_path); // Log read failure
    }

    exit; // Terminate the script after sending the file
}

// Function to create the admin menu
function db_backup_menu() {
    add_menu_page(
        'Database Backup',
        'DB Backup',
        'manage_options',
        'db-backup',
        'db_backup_page'
    );
}
add_action('admin_menu', 'db_backup_menu');

// Function to display the admin page
function db_backup_page() {
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    ?>
    <div class="wrap">
        <h1>Database Backup</h1>
        <form method="post" action="">
            <input type="hidden" name="db_backup_action" value="backup_db">
            <p>
                <input type="submit" class="button button-primary" value="Download Database Backup">
            </p>
        </form>
    </div>
    <?php

    // Debugging: Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log('Form submitted'); // Log form submission
        if (isset($_POST['db_backup_action']) && $_POST['db_backup_action'] === 'backup_db') {
            error_log('Backup action triggered'); // Log backup action
            db_backup_download();
        } else {
            error_log('Backup action not set'); // Log if action is not set
        }
    }
}

// At the end of your plugin, you can flush the buffer if needed
ob_end_flush();
