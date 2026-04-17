# Evaluator Demo Workflow

Run the seed script first:

```bash
php scripts/demo/setup_evaluator_demo.php
```

Demo credentials:

- Main presenter account: `demo.ava@skillxchange.local` / `DemoPass123!`
- Live request/session partner: `demo.ben@skillxchange.local` / `DemoPass123!`
- Older terminated chat partner: `demo.cara@skillxchange.local` / `DemoPass123!`
- Wallet history and skill-debt partner: `demo.dilan@skillxchange.local` / `DemoPass123!`

Suggested walkthrough:

1. Sign in as `Ava Demo`.
   After login, use the left sidebar and click `Your Matches`.

2. Show matches fetched.
   Point out:
   `Ben Match` is the live mutual match for the request flow.
   `Dilan History` is an already connected match with prior completed sessions.
   `Cara Archive` is available as an older relationship with chat history.

3. Send request.
   On `Your Matches`, click `Connect` on `Ben Match`.

4. Accept request.
   Sign out, sign in as `Ben Match`, open `Your Matches`, and use the `Connection Requests` card to accept Ava's request.

5. Open session.
   Still as `Ben Match`, click `Go to Chat`.

6. Choose session method.
   In the chat header, click `Start Session`.
   In the modal, show the `Payment Type` options:
   `BuckX (Virtual Currency)`
   `SkillX (Skill Debt)`

7. Offer.
   For the live path, pick either:
   `BuckX` with an amount such as `25`
   `SkillX` with skill `github`
   Then click `Send Offer`.

8. Accept offer.
   Sign out, sign back in as `Ava Demo`, open `Sessions`, enter the `Ben Match` chat, and click `Accept`.

9. Message sharing.
   Use the same chat to send a message from Ava.
   If you want to show two-way messaging, sign back into Ben and reply once.

10. Show older chats (terminated).
   As `Ava Demo`, open `Sessions` and select the conversation with `Cara Archive`.
   Explain that this chat remains in history even though the earlier session was terminated.

11. Show auto BuckX transfer in wallet.
   As `Ava Demo`, open `Wallet`.
   The seeded data already shows a completed BuckX transfer to `Dilan History`, so Ava's balance is reduced and the sent transaction is listed in history.

12. Show skill debt in wallet.
   On the same `Wallet` page, point to the `Skill Debts` table.
   Ava has an active debt of `1.50 hrs` of `marketing` owed to `Dilan History`.

13. Show how debt is used in a match.
   Go back to `Sessions` with `Ben Match`, click `Start Session`, choose `SkillX (Skill Debt)`, and explain that accepting and completing this type of matched session creates a wallet debt record just like the seeded `Dilan History` example.

Recommended narration:

- Use `Ben Match` for the live request and offer flow.
- Use `Cara Archive` for the terminated-history example.
- Use `Dilan History` for the already-completed BuckX transfer and active skill-debt evidence.
