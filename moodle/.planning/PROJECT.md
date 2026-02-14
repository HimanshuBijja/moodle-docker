# Project: Behat Standardization: Implement `notification` Named Selector

## Goal
Standardize and simplify Behat assertions for Moodle notifications by implementing a new named selector `notification`.

## Context
Moodle currently uses various CSS and XPath selectors to assert the presence of notifications (alerts). Following the recent introduction of the `toast_message` selector (MDL-87443), we aim to provide a similar standard for standard Moodle alerts.

## Tech Stack
- PHP (Moodle Core)
- Behat (Mink / Selenium)
- XPath
