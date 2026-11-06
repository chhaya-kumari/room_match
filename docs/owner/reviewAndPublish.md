# Review & Publish PG Module

## 1. Purpose
Allows owners to review saved PG information, check listing readiness, identify missing requirements, and submit a complete draft for review.

## 2. Database
Uses data from:
- `pgs` — basic details, listing status, and verification status.
- `rooms` — room availability.
- `amenities` and `pg_amenities` — selected amenities and billing settings.
- `charges` — charges for separately billed amenities.
- `pg_images` — uploaded image records.

## 3. Workflow
1. Validate the PG ID and verify ownership.
2. Retrieve saved PG details, rooms, amenities, charges, and images.
3. Check listing readiness and return missing requirements.
4. On submission, verify the CSRF token and recheck readiness.
5. If all requirements pass, update the listing status and redirect with a flash message.

## 4. MVC Responsibilities and Files

### Controller
- `public/owner/review-pg.php` - Handles requests, authentication, CSRF, errors, flash messages, and redirects. 

### View
- `views/owner/review-pg-form.php` - Displays the saved PG information, readiness checklist, and submission action. 

  -- ucwords convert - multi_sharing into Multi Sharing. for better readability; 

### Service
- `src/services/PGSubmissionService.php` - Coordinates models, evaluates readiness, and enforces submission rules. 

### Models
- `PG` — retrieves PG details and updates submission status.
- `Room` — retrieves rooms and available-bed information.
- `Amenity` — retrieves selected amenities and billing settings.
- `Charge` — retrieves saved charges.
- `PGImage` — retrieves image records.
- `AuthorizationService` — verifies PG ownership.

## 5. Key Decisions
- **Mandatory requirements:** PG name, address, valid location, at least one available bed, at least one amenity, valid charges for separately billed amenities, and at least one image.
- **Optional details:** Description, rules, notice period, and lock-in period do not block submission.
- **Available beds:** Calculated as `capacity - occupied_beds`, not stored separately.
- **Charge validation:** Reuses `ChargeValidator` to validate saved charge data.
- **Recheck before submission:** Readiness is recalculated when the owner submits, rather than relying on the earlier page load.
- **Submission status:** A ready draft moves to `pending` with verification status `pending`. Submission does not mean the PG is approved or published.
- **Image check:** Requires an image record; verification status is not checked.
- If the listing is ready, submit it using the existing PG::submitForReview() method.
- If it isn't ready, show the owner clear errors and links to the relevant edit pages.

## 6. Tested

- Verify authentication and cross-owner access protection.
- Test missing, invalid, and non-existent PG IDs.
- Confirm the review page displays all saved module data.
- Test each missing readiness requirement.
- Confirm zero available beds fail the bed requirement.
- Verify separately billed amenities require valid charges.
- Confirm optional details do not block submission.
- Test missing and invalid CSRF tokens.
- Confirm incomplete PGs remain drafts and ready PGs can be submitted.
- Verify listing and verification statuses after submission.
- Confirm unexpected errors are logged without exposing internal details.

## 7. Problems

* Problem 1: `$summary` was empty & not receiving the intended data (summary from the pg model method).

- Cause : In controller (review-pg.php) the GET condition is written incorrectly because of that PGSubmissionService is not being called on a normal page load, leading to `$summary` being empty.

- Solution : fixed the GET & POST condition check.

- Learning : Always check basic yet crucial code.
