# Requirements: Behat Notification Selector

## Overview
Implement a named selector `notification` in Moodle's Behat framework to simplify asserting the presence of alerts/notifications.

## Functional Requirements
1.  **Selection Logic:**
    *   Target elements with the CSS class `alert`.
    *   Match based on the text content of the notification.
    *   Support partial text matching (since notifications often contain close buttons or icons).
2.  **Standard Classes Supported:**
    *   `alert-success`
    *   `alert-info`
    *   `alert-warning`
    *   `alert-danger`
3.  **Standardization:**
    *   Refactor existing `.feature` files to use `"Message" "notification"` instead of CSS or XPath selectors.

## Technical Requirements
1.  Modify `moodle/lib/behat/classes/partial_named_selector.php`.
2.  Add `notification` to `$allowedselectors`.
3.  Add `notification` XPath definition to `$moodleselectors`.
