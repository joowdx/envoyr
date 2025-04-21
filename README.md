<p align="center">
  <a href="https://envoyr.com" target="_blank">
    <img src="https://raw.githubusercontent.com/jaslup16/envoyr/master/envoyr-logo.png" width="400" alt="Envoyr Logo">
  </a>
</p>



## Envoyr System Overview

Envoyr is a modern document tracking system designed to streamline how organizations manage, trace, and verify physical or digital documents throughout their lifecycle. Built with simplicity and efficiency in mind, Envoyr integrates powerful features like QR code generation, real-time location tracking, and detailed status updates to ensure complete visibility and accountability.

Whether you're handling sensitive legal paperwork, internal memos, or high-volume correspondence, Envoyr provides a secure, centralized platform to monitor movement, prevent loss, and maintain records with confidence.

Key Features:

📦 Document Journey Tracking: Visualize every stop in a document’s path—from sender to recipient.

📍 Location Monitoring: Track handoffs with QR-Scanner checkpoints for enhanced transparency

🔐 Secure QR Codes: Attach smart QR codes to documents for instant access and authentication.

🌓 Dark/Light Mode: Seamless user experience with theme switching for comfort and accessibility.

👥 User Testimonials: Real feedback to build trust and showcase success stories.

🛠️ Built with: HTML, Tailwind CSS, Font Awesome, and a modern tech stack.

Envoyr is ideal for businesses, government agencies, schools, or anyone needing reliable document movement control—bringing peace of mind and precision to paper trails.

## How Envoyr Works?

1. QR Code Generation:
The originating office generates a unique QR code and attaches it to the document. This code serves as the primary means of tracking the document's journey.

2. Assigning a Destination Office:
The system or sender assigns the intended receiving office where the document should be delivered.

3. QR Code Scanning:
Upon receipt, the receiving office scans the document’s QR code. This action logs the location and confirms the document’s arrival at the designated office.

4. System Notification:
The system sends a real-time notification to registered users, indicating where and when the document was last scanned.

5. Receiving Office Decision:
The receiving office evaluates the document and decides:

    To return it via official delivery, or

    To hold it for pickup by the originating office or another party.

6. Final Document Receipt:
The document completes its journey when it is successfully returned or picked up by the original creator or authorized recipient.

## Flowchart
```mermaid
flowchart TD
    n11(["Start"]) --> n15["Document Creation"]
    n15 --> n16["Record Creator Info"]
    n16 --> n17{{"Input Data (DocName, DocType, Source, Details, Select Receiving Office(s), Generate QR Code)"}}
    n17 --> n18["Is Document for Dissemination only?"]
    n18 -- No --> n20["Assign Liaison and Scan their ID"]
    n18 -- Yes --> n19["Is Document == Soft Copy"]
    n19 -- Yes --> n21["Disseminate Soft Copy thru Web App"]
    n19 -- No --> n20
    n20 --> n22["Liaison Delivers Document(s)"]
    n22 --> n23["Receiving Office(s) Scans Document QR Code"]
    n23 --> n24["Receiving Office(s) Processes the Document(s) (Reviews, Edits, Logs)"]
    n24 --> n25["Document Needs to be Forwarded to a Different Office?"]
    n25 -- Yes --> n20
    n25 -- No --> n26["Receiving Office is Returning Document By Delivery?"]
    n26 -- No --> n27["Originating Office Liaisons pickups the Document and Deliver it back to their office"]
    n27 --> F["Documents is in its Finalized State/No Further Action is Required?"]
    F -- Yes --> G["END"]
    F -- No --> n20
    n21 --> G
    n26 -- Yes --> n28["Originating Office Receives Document"]
    n28 --> F
```
## ER Diagram
```mermaid
erDiagram
Document {
ulid id
string code
string title
ulid classification_id
ulid user_id
ulid office_id
ulid section_id
ulid source_id
datetime created_at
boolean directive
}
Classification {
ulid id
string name
string description
}
Office {
ulid id
string name
string head_name
string designation
string acronym
}
Sources{
ulid id
string name
}
Section {
ulid id
string name
ulid office_id
string head_name
string designation
}
Transmittal {
ulid id
ulid document_id
ulid from_office_id
ulid to_office_id
ulid from_section_id
ulid to_section_id
int from_user_id
int to_user_id
text remarks
datetime received_at
boolean pick_up
}
Contents {
ulid id
ulid transmittal_id
int copies
int pages_per_copy
string control_number
string particulars
string payee
double amount
}
User {
ulid id
ulid office_id
ulid section_id
string name
string email
string password
string role
string avatar
}
Attachments{
ulid id
string remarks
json files
json paths
ulid attachable_id
string attachable_type
}

    Transmittal }|--|| Document : "includes"
    Section || -- }| User : "has"
    Office || -- }| User : "has"
    Contents }|--|| Transmittal : "under"
    Transmittal }|--|| User : "sent by"
    Transmittal }|--|| User : "received by"
    Office ||--o{ Section : "has"
    Document }| -- || User : "can make"
    Office || -- }| Document : "can make"
    Section || -- }| Document : "can make"
    Classification ||--|{ Document : "classified as"
    Document }| -- o| Sources : "can have"
    Document || -- }| Attachments : "has many"
    Transmittal || -- }| Attachments : "has many"
``` 
