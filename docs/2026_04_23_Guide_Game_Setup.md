# Guide: Creating Courses and Starting Games

This guide explains how teachers can create their own courses with players and start games. It also covers how players can create games on their own and invite others.

**Prerequisite:** An admin has already created a teacher account and shared the login credentials.

## 1. Log In

1. Open the app URL in a browser.
2. In the footer, click **"Login für Lehrpersonen"** (Login for Teachers) — this opens the admin interface (`/admin`).
3. Log in with email and password.

## 2. Add Players

There are two options: create players manually or import them from a CSV file. With the CSV option, players are imported after creating the course (see section 4).

### Option 1: Create Players Manually

1. In the left navigation, click **"Spieler:innen"** (Players).
2. Click **"Erstellen"** (Create) in the top right.
3. Fill in the form:
   - **SoSciSurvey ID** — The player's identifier. Intended for SoSciSurvey IDs, but any unique username works (no email validation — it does not have to be an email address).
   - **Passwort** (Password) — A password for the player.
   - **Kann Spiele erstellen** (Can Create Games) — Optional toggle. Allows the player to create games on their own.
4. Click **"Erstellen"** (Create) or **"Erstellen & weiterer Eintrag"** (Create & Add Another) to continue adding players.
5. Repeat steps 2–4 for all players.

Then continue with section 3 (Create a Course).

### Option 2: Prepare a CSV File

Create a CSV file with semicolon (`;`) as the delimiter and three columns:

| SoSciSurveyId | Passwort | Kann Spiele erstellen |
|---|---|---|
| player1 | password123 | false |
| player2 | password456 | false |
| player3 | password789 | true |

Notes:
- **The delimiter is semicolon** (`;`), not comma.
- **SoSciSurveyId** is intended for SoSciSurvey IDs, but any unique username works (no email validation).
- **Kann Spiele erstellen** (Can Create Games) uses `true` or `false`.
- If a player with the same SoSciSurvey ID already exists, their data is updated (no duplicates are created).

The import happens after creating the course (section 4).

## 3. Create a Course

1. In the left navigation, click **"Kurse"** (Courses).
2. Click **"Erstellen"** (Create) in the top right.
3. Fill in the form:
   - **Name** — A name for the course (e.g. "Class 10a WS2026").
   - **Spieler:innen** (Players) — For Option 1: select the previously created players. For Option 2 (CSV import): leave empty.
4. Click **"Erstellen"** (Create).
5. You are automatically redirected to the course detail page.

## 4. Import Players from CSV (Option 2 Only)

If importing players via CSV:

1. On the course detail page, click **"Spieler:innen importieren"** (Import Players) in the top right.
2. Select the prepared CSV file.
3. Verify the column mapping is correct (SoSciSurveyId, Passwort, Kann Spiele erstellen).
4. Click **"Importieren"** (Import).
5. Reload the page after the import completes.

The imported players are now assigned to the course.

## 5. Create Games

1. On the course detail page, click **"Spiele erstellen"** (Create Games) in the top right.
2. Select **"Gruppengröße bevorzugen"** (Preferred Group Size):
   - *Bevorzugt 4er-Gruppen* — Prefer groups of 4 (default)
   - *Bevorzugt 3er-Gruppen* — Prefer groups of 3
3. Confirm.

Games are created automatically. Players are randomly shuffled into groups.

**Note:** At least 2 players are required.

### How Does Group Assignment Work?

The system tries to form groups of the preferred size. When the total number of players is not evenly divisible, groups are adjusted — no group will be smaller than 2 or larger than 4.

Examples:

| Players | Preferred Size | Result |
|---|---|---|
| 12 | 4 | 3 groups of 4 |
| 11 | 4 | 2 groups of 4 + 1 group of 3 |
| 9 | 4 | 3 groups of 3 (since 2x4 would leave 1 player alone) |
| 10 | 3 | 2 groups of 3 + 1 group of 4 |
| 5 | any | 1 group of 3 + 1 group of 2 |

## 6. Players Join the Game

1. Players open the app URL in a browser.
2. Log in with their **SoSciSurvey ID** and **Password**.
3. Select the newly created game from the game overview.
4. Each player chooses a name and a life goal.
5. Once all players in a group are ready, the game starts automatically.

---

## 7. Creating Games as a Player (Without a Teacher)

Players who have the **"Kann Spiele erstellen"** (Can Create Games) option enabled can create games themselves and invite others via link. Invited players do not need their own account.

**Prerequisite:** The "Kann Spiele erstellen" option was enabled when creating the player (by the teacher, or via CSV import with `true` in the `Kann Spiele erstellen` column).

### 7.1 Create a Game

1. Open the app URL and log in with **SoSciSurvey ID** and **Password**.
2. On the game overview, click **"Neues Spiel erstellen"** (Create New Game).
3. **Choose the number of players** — click **2**, **3**, or **4**.
4. Click **"Weiter"** (Continue).

### 7.2 Invite Other Players

After creating the game, a screen with links is shown:

- **Spieler 1 (Du)** (Player 1 — You): A **"Spiel beitreten"** (Join Game) button — this is how you enter the game yourself.
- **Spieler 2, 3, 4** (Player 2, 3, 4): A **"Link teilen"** (Share Link) button for each — this copies the invitation link to the clipboard (or opens the operating system's share dialog on mobile devices).

**Important:** Share all links with the other players first (e.g. via messenger, email, or in person) **before** joining the game yourself. Each link is unique and belongs to exactly one player slot.

Invited players do not need an account. They simply open the link they received in a browser and are taken directly into the game.

### 7.3 Start the Game

1. After all links have been shared, click **"Spiel beitreten"** (Join Game) as Player 1.
2. The invited players open their respective links.
3. Each person chooses a name and a life goal.
4. Once everyone is ready, the game starts automatically.
