# TODO for Student Profile Picture Change Feature

## Overview
Implement feature for students to upload and change their profile picture in the student portal.

## Steps

- [ ] Update db_updates.sql: Add ALTER TABLE statement to include a `profile_picture` column in `student_login` table (VARCHAR(255) DEFAULT 'sample.png').

- [ ] Create uploads/profiles/ directory: Use execute_command to create the directory for storing uploaded profile images.

- [ ] Update student_portal.php:
  - Update the student info query to select profile_picture.
  - Change the profile image src to use $student['profile_picture'] ?? 'sample.png'.
  - Add file input to the update_profile form with accept="image/*".
  - Modify the POST handling for update_profile to process file upload: validate type (jpg, png, jpeg), size (< 2MB), generate unique filename (student_id_time.ext), save to uploads/profiles/, update DB with path.
  - Add enctype="multipart/form-data" to the form.
  - Handle upload errors/success in messages.

- [ ] Apply the updated db_updates.sql to the database (user to run or guide).

- [ ] Test the feature: Login as student, edit profile, upload image, verify it displays correctly and is saved.

## Followup
- Ensure directory is writable.
- Security: Validate uploads, sanitize filenames.
- If issues, debug PHP errors or use browser tools.
