# Roadmap: Behat Notification Selector

## Phase 1: Implementation
- [ ] Add `notification` to `behat_partial_named_selector::$allowedselectors`
- [ ] Add `notification` XPath to `behat_partial_named_selector::$moodleselectors`

## Phase 2: Verification
- [ ] Identify 3-5 existing tests using CSS/XPath for alerts
- [ ] Refactor these tests to use the new `notification` selector
- [ ] (Manual/CI) Verify Behat passes for these tests

## Phase 3: Wide Refactoring
- [ ] Identify all remaining cases in core
- [ ] Refactor remaining cases
