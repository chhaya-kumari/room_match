1. # Purpose of Rooms:
The Rooms module allows a PG owner to add and manage the rooms available in their PG.

Each room contains information such as its sharing type, rent, security deposit, capacity, availability, and room-specific properties.

The room information will later be used to help users compare PGs and find rooms that match their preferences.

2. # Requirements
// Functional Requirements
- An authenticated owner can add rooms to their PG.
- A PG must exist before rooms can be added to it.
- An owner can view the rooms belonging to their PG.
- An owner can update room information.
- An owner can delete a room when required.
- A room must belong to a specific PG.
- Only the owner of the PG should be allowed to manage its rooms.
- Room information should be validated before being stored.
- Room rent and security deposit should be stored separately.
- Room-specific charges should be handled according to the project's charge design.
- Room availability should be represented consistently with the database design

// Room Information based on db schema

3. # Database

Pg and rooms has 1 to many realtionship since, A PG can contain multiple rooms, while each room belongs to one PG.

- Room id : unique id for each room.
- PG id : to know which pg this room belongs to.
- Room type : represents the sharing type of room which will later be used in search/filter .
- Capacity : capacity is the actual occupancy capacity of the room and tells us how many beds the room actually contains.
- Occupied beds : represents currently occupied beds. ** occupied bed should be less than capacity. 
- Rent : rent applicable to the room offering/occupancy arrangement. this is not total rent.
- Security deposit : one time charge
- Bathroom type : type of bathroom, the room is offering and later it will be use for search/filter 
- Description : optional description about rooms.
- Window Ventilation : whether a room has ventilation system or not. useful feature for recommendation system later.
- Available beds : available beds is calculated value and it's not suppossed to be stored because the it may create synchronization problem.

// Important Design Consideration

- each room is associated with a PG through foreign key/ refrence.
- the pg_id refrences the parent PG record.
- Room rent : represents the recurring rent associated with the room.
- Security deposit : one-time cost , which is treated seperately from montly rent.
- Available Beds : need to be calculated not stored.

** the cascade is useful in the table because if a PG is deleted then its rooms get deleted automatically.

4. # Application Flow 

A. Adding Rooms
- The owner completes the PG Details step and is redirected to the Rooms page.
- The Rooms controller receives the pg_id.
- The application obtains the authenticated owner's ID from the session.
- The application verifies that the selected PG belongs to the owner.
- The room form is displayed.
- On submission, the controller verifies the CSRF token and processes the request.
- RoomService.php validates the submitted values and applies the business rules.
- Room.php executes the database insert using PDO and a prepared statement.
- The application displays success feedback and redirects to the appropriate Rooms page.

B. Viewing Rooms
- The owner opens the Rooms page for a selected PG.
- The application verifies access to that PG.
- The model retrieves its associated rooms.
- The page displays the room information, including calculated available beds where - implemented.

C. Editing Rooms
- The owner selects a room to edit.
- The application retrieves the room using its room_id.
- The application verifies that the room belongs to a PG owned by the authenticated owner.
- The existing room information is loaded into the form.
- The submitted values are checked for a valid CSRF token and validated.
- The model updates the room record.
- The application provides feedback and redirects to the appropriate Rooms page.

D. Deleting Rooms
- he owner requests deletion of a room.
- The application validates the request and CSRF token.
- The application verifies the room's ownership through its parent PG.
- The model deletes the room record if the request is authorized.
- The application provides feedback and redirects appropriately.

5. # Files & Responsiblites:

* Model

**File:** `src/models/Room.php`

Responsibilities:
- Retrieve existing PG details.
- Update the relevant fields in the `pgs` table.
- Execute database queries using the project's PDO-based model structure.
 -> handles : SQL, INSERT, SELECT, UPDATE, DELETE  

* Controller

**File:**  `public/owner/rooms.php`.

Responsiblities :
 - request method
 - pg_id
 - CSRF security
 - session owner
 - reading from data
 - redirecting
 - displaying errors

* View -

**File:**  `views/owner/room-form.php`.

Responsiblities :
- room form
- room list
- displaying calculated available beds
- validation messages

* Service - 

**File:**  `public/owner/RoomService.php`.

Responsiblities :
- business validation
- checking that pg belongs to logged-in owner
- validating room type/capacity/ occupancy / pricing
- calculating/ returning room realted data
- deciding what operation is allowed 

6. # Input Validation and Security - 

* Validator
**File:** `src/validators/RoomValidator.php`

Responsiblities :
- validate supported room type and other inputs. 
- validate occupied beds that they don't exceed capacity and they are non zero.
- validate available beds are non zero and it should not exceed capacity.

* Authorization
Responsibilities :
- room ownership check
- pg_id , owner_id must be validated and verified properly and owner should not be able to manage someone else's pg.

* CSRF protection
- state changing request should validate csrf token.
- invalid token must stop processing.

* Database Safety 
- using PDO prepared statements.
- validating identifiers before performing operations.

7. # Testing

Create a room with valid data. 
Edit & Delete an existing room.
Reject unauthorized room-management access.
Reject an invalid CSRF token.
Prevent occupied beds from exceeding capacity.
Reject invalid data.
Available beds are calculated correctly
Invalid or missing room identifiers are handled correctly
One owner cannot edit or delete another owner's room
The page redirects correctly after successful operations

8. # Problems and Solutions

// problem 1
Problem: Incorrect Occupancy Column Name
Cause: The model referenced occupancy, but the database column was occupied_beds.
Solution: Corrected the model to use occupied_beds.
Learning: Keep model queries aligned with the database schema and test database operations after schema-related changes.

// for edit-room.php :
// during test error 1 encountered
// No, the reason your previous values are not displaying is due to a variable assignment mismatch in your GET request logic.In your GET block, you call $authorizationService->verifyRoomOwnership($roomId, $ownerId) and assign the result to a variable named plural $rooms instead of singular $room. Because your HTML form relies entirely on $room, it finds nothing, and your form fields load completely blank.Additionally, in your POST block, if validation fails (inside the catch block), you attempt to reload the room data, but if $room was already populated with submitted form data, your catch block overwrites it completely by fetching the old database version again, wiping out what the user just typed.