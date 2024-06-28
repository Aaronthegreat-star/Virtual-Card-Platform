# vCardPlatform_2023

## Overview

The vCard Platform is a web application that allows users to create and manage their virtual debit cards (vCards). The project aims to implement a Single Page Application (SPA) for the vCard platform, using the Vue.js framework for the web client and a backend developed with Laravel (RESTful API) and Node.js (WebSocket server).

## Features

1. **vCard Creation and Dismissal, Authentication, Profile, Settings**:
   - Users can create a new vCard by providing a phone number, password, name, email, and optional photo.
   - Users can authenticate and access their vCard using the phone number and password.
   - Users can update their profile information, change the confirmation code, and password.
   - Users can configure the categories for credit (income) and debit (expense) transactions.
   - Users can dismiss (remove) their vCard if the balance is zero.

2. **Transactions**:
   - Users can perform debit transactions, which require the transaction value, payment type, and reference.
   - The application ensures that the transaction value is valid (greater than zero, less than or equal to the vCard balance and maximum limit).
   - The application supports various payment types, including vCard, MBWAY, PayPal, IBAN, MB, and Visa.
   - The application automatically updates the vCard balance and records the transaction details, including the old and new balance.
   - Users can access the vCard's transaction history, which is sorted by datetime.
   - Transactions can have an optional description and category, which can be added or modified after the transaction is created.
   - Transactions cannot be deleted, but they can be "soft deleted" when the vCard is dismissed.

3. **Administration**:
   - Platform administrators can access the application with different credentials (email and password).
   - Administrators can manually add credit transactions to any vCard on the platform, using the same payment types as debit transactions (except vCard).
   - Administrators can view and modify their profile information, including their name and password.

## Architecture

The project follows a client-server architecture:

1. **Client Application (Vue.js)**:
   - The client-side application is developed using the Vue.js framework.
   - The application source code is partitioned into several JavaScript files, which are then bundled and delivered by a web server.

2. **Backend API (Laravel)**:
   - The backend API is implemented using the Laravel framework, providing a RESTful API to handle data storage and retrieval.
   - The API interacts with a MySQL database to store and retrieve data.

3. **WebSocket Server (Node.js)**:
   - The project includes a WebSocket server implemented using Node.js, which establishes a full-duplex communication channel between the client application and the server.
   - The WebSocket server acts as an intermediary between multiple instances of the client application, enabling real-time communication.

The architecture allows for potential extensions, such as the use of NoSQL databases, cache technologies, or queue servers to optimize performance and handle asynchronous operations.

## Database

The project's database schema and table structures are not provided in the given information. The user should refer to the section "8. Database" in the original document for more details on the database design.

## Deployment

The project can be deployed independently, as the backend (Laravel and Node.js) is a mandatory part of the implementation.

## Limitations

The information provided in the uploaded file does not cover the following aspects:

- Detailed database schema and table structures.
- Specific implementation details for the Vue.js client application and the Laravel/Node.js backend.
- Handling of the confirmation step for creating a new vCard.
- Integration with the external "Payment Gateway Service" API.

## Conclusion

The vCard Platform is a comprehensive web application that allows users to create and manage their virtual debit cards. The project leverages a client-server architecture, with a Vue.js-based client application and a backend developed using Laravel and Node.js. The application provides features for vCard creation, transaction management, and administration functionalities.

