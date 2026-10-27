---
paths:
  - 'plans_completed/**'
---

# Plans Completed

## Record completed plans in plans_completed/
When an implementation plan is completed, write it up as a markdown file in `plans_completed/` at the repo root, named `YYYY-MM-DD-HHMM-short-kebab-summary.md` (timestamp + short summary of the work). Include: context/why, what changed, files touched, verification performed, and follow-ups.

Skip the file when the plan is completely contained in one Linear ticket: the ticket already holds the context and plan, so record the outcome there instead. Add a closing comment to that ticket covering what changed, files touched, verification and follow-ups. Write a `plans_completed/` record only when a plan spans several tickets or has no ticket.
