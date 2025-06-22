<?php
require 'C:\xampp\htdocs\conn.php';

// Soft delete
if (isset($_POST['delete'])) {
    $id = (int)$_POST['delete'];
    $stmt = $pdo->prepare("UPDATE tasks SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Add new task
if (!empty($_POST['task'])) {
    $task = htmlspecialchars(trim($_POST['task']));
    $stmt = $pdo->prepare("INSERT INTO tasks (description, created_at) VALUES (?, NOW())");
    $stmt->execute([$task]);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Edit task
if (!empty($_POST['edit_id']) && !empty($_POST['edit_task'])) {
    $id = (int)$_POST['edit_id'];
    $task = htmlspecialchars(trim($_POST['edit_task']));
    $stmt = $pdo->prepare("UPDATE tasks SET description = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$task, $id]);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Toggle completion
if (isset($_POST['toggle'])) {
    $id = (int)$_POST['toggle'];
    $stmt = $pdo->prepare("UPDATE tasks SET is_completed = NOT is_completed, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch active tasks
$stmt = $pdo->query("SELECT * FROM tasks WHERE deleted_at IS NULL ORDER BY created_at DESC");
$tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>To-Do List App</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      box-sizing: border-box;
    }
    .container {
      width: 100%;
      min-height: 100vh;
      background: linear-gradient(135deg, #153677, #4e085f);
      padding: 10px;
    }
    .todo-app {
      width: 100%;
      max-width: 540px;
      background: #fff;
      margin: 100px auto 20px;
      padding: 40px 30px 70px;
      border-radius: 10px;
    }
    .todo-app h2 {
      color: #002765;
      margin-bottom: 20px;
      text-align: center;
    }
    form.row {
      display: flex;
      gap: 10px;
      margin-bottom: 25px;
    }
    form.row input[type="text"] {
      flex-grow: 1;
      padding: 12px 15px;
      font-size: 16px;
      border: 1px solid #bbb;
      border-radius: 5px;
    }
    form.row button {
      padding: 12px 25px;
      background: #ff5945;
      color: white;
      font-weight: bold;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }
    ul {
      list-style: none;
    }
    ul li {
      padding: 15px;
      background: #f9f9f9;
      margin-bottom: 10px;
      border-radius: 5px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }
    .task-left {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-grow: 1;
    }
    .task-actions {
      display: flex;
      gap: 10px;
      align-items: center;
    }
    .task-text {
      font-size: 16px;
      color: #222;
    }
    .completed {
      text-decoration: line-through;
      color: #999;
    }
    .delete-btn {
      color: #ff5945;
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
    }
    .edit-link {
      text-decoration: none;
      font-size: 14px;
      color: #444;
    }
    .edit-form {
      margin-top: 10px;
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .edit-form input[type="text"] {
      flex: 1;
      padding: 8px;
      font-size: 14px;
      border: 1px solid #bbb;
      border-radius: 4px;
    }
    .edit-form button {
      padding: 8px 14px;
      font-size: 14px;
      border: none;
      border-radius: 4px;
      background: #ff5945;
      color: #fff;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="todo-app">
      <h2>To-Do List</h2>

      <!-- Add Task -->
      <form method="POST" class="row">
        <input type="text" name="task" placeholder="Add your task..." required />
        <button type="submit">Add</button>
      </form>

      <ul>
        <?php if (empty($tasks)): ?>
          <li>No tasks yet!</li>
        <?php else: ?>
          <?php foreach ($tasks as $task): ?>
            <li>
              <div class="task-left">
                <!-- Completion checkbox -->
                <form method="POST" style="margin: 0;">
                  <input type="hidden" name="toggle" value="<?= $task['id'] ?>">
                  <input type="checkbox" onchange="this.form.submit()" <?= $task['is_completed'] ? 'checked' : '' ?>>
                </form>

                <?php if (isset($_GET['edit']) && $_GET['edit'] == $task['id']): ?>
                  <!-- Edit Form -->
                  <form method="POST" class="edit-form">
                    <input type="hidden" name="edit_id" value="<?= $task['id'] ?>">
                    <input type="text" name="edit_task" value="<?= htmlspecialchars($task['description']) ?>" required />
                    <button type="submit">Save</button>
                  </form>
                <?php else: ?>
                  <span class="task-text <?= $task['is_completed'] ? 'completed' : '' ?>">
                    <?= htmlspecialchars($task['description']) ?>
                  </span>
                <?php endif; ?>
              </div>

              <div class="task-actions">
                <?php if (!isset($_GET['edit']) || $_GET['edit'] != $task['id']): ?>
                  <a class="edit-link" href="?edit=<?= $task['id'] ?>">✏️edit</a>
                <?php endif; ?>
                <form method="POST">
                  <input type="hidden" name="delete" value="<?= $task['id'] ?>">
                  <button type="submit" class="delete-btn" onclick="return confirm('Delete this task?');">&times;</button>
                </form>
              </div>
            </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</body>
</html>
