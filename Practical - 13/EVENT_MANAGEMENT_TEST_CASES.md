# Event Management — screenshot & test checklist

Save un-cropped screenshots of the **entire browser result**. Do not include passwords or session cookie values.

| Figure | Test | Expected result |
| --- | --- | --- |
| 1 | phpMyAdmin import `event_management_schema.sql` | Successful migration; old data remains |
| 2 | Admin Dashboard | **Manage Events & Posters** link is visible |
| 3 | Event Management list | Existing and six new sample events shown |
| 4 | Add Event form with sample poster | Filled event form before submission |
| 5 | INSERT | New event saved with success message and preview |
| 6 | UPDATE | Modified event details displayed with success |
| 7 | Category filter | Only events in selected category |
| 8 | Open/Closed filter | Records filtered by status |
| 9 | Valid poster upload | Image thumbnail renders and saved poster path in MySQL |
| 10 | Invalid file validation | Select `.txt` file by All Files chooser (or rename a plain text file `.jpg`); backend rejects real MIME |
| 11 | Oversized file validation | Select an image larger than 2 MiB; backend rejects |
| 12 | Delete confirmation + archive | Archived event removed from active list; student registrations preserved |
| 13 | Archived filter / Restore | Event marked archived; Restore returns it |
| 14 | Event Change Audit | Change log shows admin actions |
| 15 | Final phpMyAdmin / SQL export | `events` and `event_change_audit` data/structure |

**Note:** Only mark a test Passed after testing it locally with XAMPP. The JavaScript confirmation dialog can be shown in its own screenshot before archiving.
