const CROSSWORD_STORAGE_KEY = "crosswordSession";
const CROSSWORD_PENDING_KEY = "crosswordPendingSave";
const CROSSWORD_API_URL = "crossword-database.php";

function crosswordDefaultSession() {
  return {
    game_name: "Crossword",
    client_id: null,
    total_time_seconds: 0,
    current_level: 1,
    levels_passed: 0,
    level_times: {
      level1: 0,
      level2: 0,
      level3: 0,
      level4: 0,
      level5: 0,
    },
    is_completed: false,
    last_saved_level: 0,
    saving_level: 0,
  };
}

function crosswordLoadSession() {
  try {
    const saved = JSON.parse(localStorage.getItem(CROSSWORD_STORAGE_KEY) || "null");
    if (saved && saved.game_name === "Crossword") {
      return {
        ...crosswordDefaultSession(),
        ...saved,
        level_times: {
          ...crosswordDefaultSession().level_times,
          ...(saved.level_times || {}),
        },
      };
    }
  } catch (error) {
    console.error(error);
  }

  return null;
}

function crosswordSaveSession(session) {
  localStorage.setItem(CROSSWORD_STORAGE_KEY, JSON.stringify(session));
}

function crosswordPrepareLevel(levelNumber) {
  if (levelNumber === 1) {
    crosswordSaveSession(crosswordDefaultSession());
    crosswordRetryPendingSave();
    return true;
  }

  const session = crosswordLoadSession();
  if (!session || session.levels_passed < levelNumber - 1) {
    const allowedLevel = session ? Math.min(session.levels_passed + 1, 5) : 1;
    window.location.href = `level${allowedLevel}.php`;
    return false;
  }

  crosswordRetryPendingSave();
  return true;
}

function crosswordStartLevel(createGrid) {
  const levelNumber = Number(window.CROSSWORD_LEVEL_NUMBER || 1);
  if (crosswordPrepareLevel(levelNumber)) {
    createGrid();
  }
}

async function crosswordCompleteLevel(levelNumber, totalTime, timeLeft) {
  let session = crosswordLoadSession() || crosswordDefaultSession();

  if (levelNumber <= session.last_saved_level || levelNumber === session.saving_level) {
    return session;
  }

  const timeSpent = Math.max(0, Number(totalTime) - Number(timeLeft));
  const levelKey = `level${levelNumber}`;
  session.level_times[levelKey] = timeSpent;
  session.total_time_seconds = Object.values(session.level_times).reduce(
    (sum, seconds) => sum + Number(seconds || 0),
    0,
  );
  session.levels_passed = levelNumber;
  session.current_level = Math.min(levelNumber + 1, 5);
  session.is_completed = levelNumber === 5;
  session.saving_level = levelNumber;
  crosswordSaveSession(session);

  const payload = crosswordPayloadFromSession(session, levelNumber);
  localStorage.setItem(CROSSWORD_PENDING_KEY, JSON.stringify(payload));

  try {
    await crosswordPostProgress(payload);
    session = crosswordLoadSession() || session;
    session.last_saved_level = Math.max(Number(session.last_saved_level || 0), levelNumber);
    session.saving_level = 0;
    crosswordSaveSession(session);
    localStorage.removeItem(CROSSWORD_PENDING_KEY);
  } catch (error) {
    session = crosswordLoadSession() || session;
    session.saving_level = 0;
    crosswordSaveSession(session);
    console.error(error);
  }

  return session;
}

function crosswordPayloadFromSession(session, levelNumber) {
  return {
    level: levelNumber,
    time_seconds: session.total_time_seconds,
    levels_passed: session.levels_passed,
    is_completed: session.is_completed ? 1 : 0,
    time_per_level: session.level_times,
  };
}

async function crosswordRetryPendingSave() {
  let payload = null;
  try {
    payload = JSON.parse(localStorage.getItem(CROSSWORD_PENDING_KEY) || "null");
  } catch (error) {
    console.error(error);
  }

  if (!payload) return;

  try {
    await crosswordPostProgress(payload);
    const session = crosswordLoadSession();
    if (session) {
      session.last_saved_level = Math.max(Number(session.last_saved_level || 0), Number(payload.level || 0));
      session.saving_level = 0;
      crosswordSaveSession(session);
    }
    localStorage.removeItem(CROSSWORD_PENDING_KEY);
  } catch (error) {
    const session = crosswordLoadSession();
    if (session) {
      session.saving_level = 0;
      crosswordSaveSession(session);
    }
    console.error(error);
  }
}

async function crosswordPostProgress(payload) {
  const response = await fetch(`${CROSSWORD_API_URL}?action=save-level&_=${Date.now()}`, {
    method: "POST",
    credentials: "same-origin",
    cache: "no-store",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });
  const data = await response.json().catch(() => ({}));

  if (!response.ok || !data.success) {
    throw new Error(data.message || "Crossword progress could not be saved.");
  }

  return data;
}
