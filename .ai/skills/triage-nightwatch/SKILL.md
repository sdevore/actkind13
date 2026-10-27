---
name: triage-nightwatch
description: "Review new or reopened Nightwatch exceptions and errors for ActKind Online, open or update the matching Linear ticket in the ActKind project, and write an initial plan to resolve or address each one. Use when the user asks to triage, review, check or go through Nightwatch issues, errors or exceptions, asks what's new in Nightwatch, or wants Linear tickets for production errors. Stops at the plan: it does not write code, open branches or change Nightwatch status without the user's go-ahead."
---

# Triage Nightwatch

A repeated triage pass: find what Nightwatch has flagged since the last pass, diagnose each issue against the code, make sure each one has a properly filled-in Linear ticket, and leave an initial plan on that ticket. The output is tickets and plans, not fixes.

## Fixed context

- **Nightwatch application:** ActKind Online, `9f362270-fd01-4a48-8274-7185553489c0`
- **Environments:** Production `9f362270-ff68-4840-939e-2723b9bda25e`, Develop (staging) `a16757b0-a59c-4763-b113-e458989e6b8a`
- **Linear:** team `SCiD Solutions`, project `ActKind`, ticket prefix `SCI-`
- **Linear statuses:** Backlog, Todo, In Progress, In Review, Done, Canceled, Duplicate
- **Labels:** area `Api` / `Web` / `Admin` / `iOS` (plain labels, combine when an error spans areas), type `Bug` / `Improvement` / `Feature`, audience `Guest` / `Member`

If an ID above stops resolving, look it up again with `list_applications` / `list_environments` / `list_issue_statuses` / `list_issue_labels`, and tell the user so this file can be updated.

**Nightwatch's Linear integration files tickets automatically.** They're titled `Error: <exception message>` and have no project, labels, creator or description (e.g. SCI-48, SCI-88). Treat them as stubs to fill in, not as finished tickets, and never create a second ticket next to one.

## Step 1: Collect

1. `list_issues` for the application with `status: open`, once per environment (Production first). Page through with `cursor` until done.
2. Also run `list_issues` with `status: resolved` and look for issues whose last seen time is after they were resolved. Those are regressions or fixes that haven't shipped yet. Note them, but don't reopen anything.
3. For each issue, `get_issue` (with the right `environment_id`) for the stack trace, request, user, queries, logs, occurrence stats and activity log.

Present a short table before going further: ref, environment, type, title (shortened), first seen, last seen, 24h / 7d counts, users affected. If there's nothing open or regressed, say so and stop.

## Step 2: Match each issue to Linear

For each Nightwatch issue, search Linear (`list_issues`, team `SCiD Solutions`, `includeArchived: true`) by:

- the exception message, or a distinctive fragment of it (the auto-filed title is `Error: <message>`),
- the exception class short name,
- `Nightwatch #<ref>` / `Nightwatch: #<ref>` (how hand-written tickets cite it, e.g. SCI-90, SCI-112).

Then classify:

- **Stub:** an auto-filed ticket with no project, labels or description. Fill it in (Step 4).
- **Tracked:** a hand-written or already-enriched ticket. Add a comment with the new occurrence data, plus anything the diagnosis changes. Don't rewrite its description.
- **Closed, but recurring:** the ticket is Done or Canceled and the error is back. Check whether the fix is actually in production: `git log origin/main --grep "SCI-<n>"`, then `gh release list --limit 5` and compare the release date to the fix commit. Production only deploys on a published GitHub release, so a fix that's merged but unreleased is "not shipped yet", not a regression. Comment either way. Reopen (move to Todo) only with the user's agreement.
- **None:** create a ticket (Step 4).

## Step 3: Diagnose

Work out the cause from the code, not just the trace:

- Find the app frames in the stack trace (skip `vendor/`), open those files and read the surrounding code. When the trace is all vendor (Livewire, Filament), get the component, route or job name from the message or the request and find it in the app.
- Check `git log -p --since=<first seen minus a few days> -- <files>` for a recent change that lines up with the first-seen time.
- Use the Boost tools where they help: `database-schema` for schema questions, `search-docs` for framework behaviour, and `last-error` / `read-log-entries` only for errors that reproduce locally.
- Look at who triggered it: authenticated user or guest, IP and country, user agent, referer, payload. Then decide whether it's a **real defect**, **bot or probe traffic** (unauthenticated, a scripted user agent, a crafted payload that isn't possible through the real UI, scanner IPs), **staging or test noise** (Develop only, or failed jobs from manual Cloud tests), or **unshipped fix**. Bot traffic that causes a 500 is usually still worth a small hardening ticket (return a 4xx instead of 500), but at low priority.

Don't reproduce errors against production. Don't dispatch tinker closures on Cloud to test anything, because they fail and Nightwatch files them to Linear. Use a local Pest test or `vendor/bin/pest --agent='…'` to confirm a theory.

## Step 4: Write the ticket

**Title:** a plain description of the symptom and where it happens, not the raw exception, e.g. "Contact form 500s when a request sends file-upload data (no WithFileUploads)". When filling a stub, replace its `Error: …` title with this.

**Fields:**
- project `ActKind`
- area label(s) from the code that fails: Filament → `Admin`, `routes/api.php` / API resources → `Api`, public or member Blade/Livewire / auth / mail → `Web`
- a type label (usually `Bug`; `Improvement` for hardening against bot traffic)
- `Guest` or `Member` when the request shows who hit it
- priority (see below)
- status `Backlog` or `Todo`, never In Progress (that happens when work actually starts)
- link related tickets (`relatedTo`), and mark it duplicate when two Nightwatch issues share a root cause

**Priority:** Urgent (1) for data loss, security, or a core flow broken for real members in Production. High (2) for real users hitting it repeatedly in Production. Medium (3) for real but rare, a degraded admin-only feature, or staging-only but certain to reach Production. Low (4) for bot or probe traffic and hardening. Also set the Nightwatch issue's priority to match (`update_issue`, `priority` only).

**Description** (use real newlines, not `\n`):

~~~markdown
Nightwatch #<ref> (<Production|Develop>), <n> occurrences in 24h / <n> in 7d, <n> users affected.
First seen <date>, last seen <date>.

## What's failing

<One short paragraph: what the user or bot was doing, what broke, what they saw.>

```
<Exception class>: <message>
<first app frame, or the component/route/job>
```

## Cause

<What in our code makes this happen, with `path/to/file.php:line` references. Say how sure you are: confirmed by reading the code / a local test, or a likely theory.>

## Proposed plan

1. **Linear and branch:** move this ticket to In Progress and work on its Linear branch name.
2. <Change, with file paths.>
3. <Test: what regression test to add, and where.>
4. <Verification: Pest run, PHPStan, staging check, and the Nightwatch issue staying quiet after the release.>
5. Reference `Fixes SCI-<n>` in the commit and PR, and close out with a comment on this ticket covering what changed, files touched, verification and follow-ups. No `plans_completed/` record is needed while the work stays within this one ticket.

## Open questions

<Only if something needs the user's decision, e.g. whether to fix or just harden. Otherwise leave this section out.>
~~~

Keep the plan at the level of an initial approach: the files, the shape of the fix and the test. It's a starting point for plan mode, not a finished design. When the right answer is "don't fix", say so in the plan (for example, ignore in Nightwatch, or add a Nightwatch filter via the `configure-nightwatch` skill).

**Cross-link:** after creating or filling the ticket, add a comment to the Nightwatch issue (`add_issue_comment`) with the Linear ticket ID and URL and a one-line cause. Only comment when there's a ticket or diagnosis to record, because comments notify subscribers.

## Step 5: Report and stop

End with a summary table: Nightwatch ref → Linear ticket (created / filled / commented), classification, priority, and a one-line plan. Then list what needs the user:

- issues you'd mark **ignored** in Nightwatch, or tickets you'd **cancel** (noise, probes, test jobs). Ask before changing either.
- regressions where you'd reopen a ticket
- which ticket you'd suggest picking up first

Don't resolve or ignore Nightwatch issues, don't move tickets to In Progress, and don't start branches or code in this skill. Those happen once the user picks a ticket to work on. Resolve the Nightwatch issue only after the fix has shipped in a release, since Nightwatch reopens issues that recur before the fix is deployed.
