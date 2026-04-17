# Evaluator Demo Workflow

Run the seed script first:

```bash
php scripts/demo/setup_evaluator_demo.php
```

Demo credentials:

- Main presenter account: `nadisha@gmail.com` / `DemoPass123!`
- Live request/session partner: `kasun.demo@gmail.com` / `DemoPass123!`
- Older terminated chat partner: `tharushi.demo@gmail.com` / `DemoPass123!`
- Wallet history and skill-debt partner: `chathura.demo@gmail.com` / `DemoPass123!`

Suggested walkthrough:

1. Sign in as `Nadeesha Perera`.
   After login, use the left sidebar and click `Your Matches`.

2. Show matches fetched.
   Point out:
   `Kasun Silva` is the live mutual match for the request flow.
   `Chathura Jayasinghe` is an already connected match with prior completed sessions.
   `Tharushi Fernando` is available as an older relationship with chat history.

3. Send request.
   On `Your Matches`, click `Connect` on `Kasun Silva`.

4. Accept request.
   Sign out, sign in as `Kasun Silva`, open `Your Matches`, and use the `Connection Requests` card to accept Nadeesha's request.

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
   Sign out, sign back in as `Nadeesha Perera`, open `Sessions`, enter the `Kasun Silva` chat, and click `Accept`.

9. Message sharing.
   Use the same chat to send a message from Ava.
   If you want to show two-way messaging, sign back into Ben and reply once.

10. Show older chats (terminated).
   As `Nadeesha Perera`, open `Sessions` and select the conversation with `Tharushi Fernando`.
   Explain that this chat remains in history even though the earlier session was terminated.

11. Show auto BuckX transfer in wallet.
   As `Nadeesha Perera`, open `Wallet`.
   The seeded data already shows a completed BuckX transfer to `Chathura Jayasinghe`, so Nadeesha's balance is reduced and the sent transaction is listed in history.

12. Show skill debt in wallet.
   On the same `Wallet` page, point to the `Skill Debts` table.
   Nadeesha has an active debt of `1.50 hrs` of `marketing` owed to `Chathura Jayasinghe`.

13. Show how debt is used in a match.
   Go back to `Sessions` with `Kasun Silva`, click `Start Session`, choose `SkillX (Skill Debt)`, and explain that accepting and completing this type of matched session creates a wallet debt record just like the seeded `Chathura Jayasinghe` example.

Recommended narration:

- Use `Kasun Silva` for the live request and offer flow.
- Use `Tharushi Fernando` for the terminated-history example.
- Use `Chathura Jayasinghe` for the already-completed BuckX transfer and active skill-debt evidence.
