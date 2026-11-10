# PG Details — Owner Module

## 1. Purpose

The PG Details feature allows an authenticated PG owner to add or update the descriptive information and policies associated with an existing PG listing.

This step follows Create PG and collects information such as the PG description, gender preference, rules, notice period, and lock-in period.

The saved information is used to provide users with a better understanding of the PG before they decide whether it matches their requirements.

## 2. Requirements

### Functional Requirements

- Only authenticated owners can manage their PG details.
- The PG must already exist before its details can be updated.
- The owner must be authorized to manage the selected PG.
- The owner can add or update the PG description.
- The owner can specify the gender preference for the PG.
- The owner can define the PG rules.
- The owner can specify the notice period in days.
- The owner can specify the lock-in period in months.
- Submitted information must be validated before being saved.
- An invalid or missing `pg_id` should redirect the owner to the appropriate earlier step.
- After a successful update, the owner should be redirected to the Rooms step.
- Unauthorized owners must not be allowed to modify another owner's PG.

## 3. Database Design

### Main Table

`pgs`

The PG Details feature updates the existing PG record rather than creating a new PG.

 `pg_id`  Identifies the PG being updated.
 `description` Stores a description of the PG.
 `gender_preference` Stores the permitted gender preference for the PG.
 `rules` Stores the rules applicable to the PG.
 `notice_period_days` Stores the notice period in days.
 `lock_in_period_months` Stores the lock-in period in months.


### Relationship

The PG Details record is part of an existing PG listing. The `pg_id` identifies the listing, and the owner relationship determines who is authorized to update it.

## 4. Application Flow

1. The owner completes the Create PG step.
2. The owner is redirected to the PG Details page.
3. The controller retrieves the relevant `pg_id`.
4. The application checks that the PG exists and belongs to the authenticated owner.
5. The existing PG details are retrieved and displayed when applicable.
6. The owner submits the description, gender preference, rules, notice period, and lock-in period.
7. The controller handles the request and the service validates the submitted information.
8. The application verifies authorization before updating the record.
9. The PG model executes the database update.
10. On success, the owner receives feedback and is redirected to the Rooms step.

## 5. Files and Responsibilities

### Controller

**File:**  `public/owner/pg-details.php`.

Responsibilities:
- Handle GET and POST requests.
- Retrieve the PG identifier.
- Obtain the authenticated owner's ID.
- Coordinate validation and authorization through the appropriate services.
- Handle flash messages and redirects.

### Service

**File:** `src/services/PGService.php`

Responsibilities:
- Validate PG details.
- Coordinate the update workflow.
- Ensure that the requested PG can be managed by the authenticated owner through the established authorization mechanism.

### Model

**File:** `src/models/PG.php`

Responsibilities:
- Retrieve existing PG details.
- Update the relevant fields in the `pgs` table.
- Execute database queries using the project's PDO-based model structure.

### View

**File:** `views/owner/pg-detailsform.php`
Responsibilities:
- Display the PG Details form.
- Populate existing values when editing.
- Display validation errors and feedback as implemented.

## 6. Validation and Security

The following checks are relevant to this feature:

- Validate that `pg_id` is present and valid.
- Confirm that the PG exists.
- Verify ownership before permitting updates.
- Validate the submitted description, gender preference, rules, notice period, and lock-in period.
- Verify the CSRF token on POST requests if enforced by the implementation.
- Use PDO prepared statements for database operations.
- Redirect appropriately when the PG identifier is invalid or the owner is unauthorized.

## 7. Testing

 Valid PG details are saved  
 Missing or invalid `pg_id` - redirecting to Create PG  
 Non-existent PG - request rejected or redirected appropriately  
 Owner updates their own PG - update succeeds  
 Owner attempts to update another owner's PG - Access denied  
 Invalid notice period - validation error  
 Invalid lock-in period - validation error  
 Invalid CSRF token - request rejected, if CSRF protection is enforced  
 Successful update  redirect to Rooms  

## 8. Important Design Decisions

- Update the Existing PG
PG Details extends the PG record created during the Create PG step. It must update that existing record rather than create another PG.

- Ownership Verification
The application must verify that the authenticated owner owns the requested PG before allowing changes. This prevents one owner from modifying another owner's listing.

- Separate Responsibilities
The controller handles HTTP requests and redirects, the service coordinates business logic and validation, and the model handles SQL operations. This maintains the architecture used throughout the project.

## 9. Problems and Solutions

NO problems encountered. 


## 10. Interview Notes

**What is the purpose of the PG Details module?**

It allows an owner to add or update descriptive information and policies for an existing PG listing, including its description, gender preference, rules, notice period, and lock-in period.

**How do you prevent owners from modifying another owner's PG?**

The application verifies ownership of the requested PG before allowing the update.

**Why is `pg_id` important?**

It identifies the existing PG record that needs to be retrieved or updated, connecting this step to the earlier Create PG workflow.