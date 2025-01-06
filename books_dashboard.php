<?php
session_start();

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied: You do not have permission to access this page.");
}

// Include the database connection
include('includes/db.php');

try {
    // Fetch all books
    $query = "SELECT 
    b.ISBN, 
    a.Fname AS AuthorFirstName, 
    a.Lname AS AuthorLastName, 
    b.Title, 
    b.PubDate, 
    p.Name AS Publisher, 
    b.Cost, 
    b.Retail, 
    b.Discount, 
    b.Category
FROM 
    Books b
LEFT JOIN 
    Publisher p ON b.PubID = p.PubID
LEFT JOIN 
    BOOKAUTHOR ba ON b.ISBN = ba.ISBN
LEFT JOIN 
    Author a ON ba.AuthorID = a.AuthorID
ORDER BY 
    b.ISBN ASC;
";
    $stmt = $conn->query($query);
    $books = $stmt->fetchAll();

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'delete') {
            $isbn = $_POST['isbn'];

            $deleteQuery = "DELETE FROM Books WHERE ISBN = :isbn";
            $stmt = $conn->prepare($deleteQuery);
            $stmt->execute([':isbn' => $isbn]);
        } elseif ($action === 'edit') {
            $isbn = $_POST['isbn'];
            $title = $_POST['title'];
            $pubDate = $_POST['pubDate'];
            $pubID = $_POST['pubID'];
            $cost = $_POST['cost'];
            $retail = $_POST['retail'];
            $discount = $_POST['discount'];
            $category = $_POST['category'];

            $editQuery = "UPDATE Books 
                          SET Title = :title,
                              Retail = :retail, Category = :category 
                          WHERE ISBN = :isbn";
            $stmt = $conn->prepare($editQuery);
            $stmt->execute([
                ':isbn' => $isbn,
                ':title' => $title,
                ':retail' => $retail,
                ':category' => $category,
            ]);
        }

        header("Location: books_dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Manage Books</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --background-color: #f5f7fa;
            --text-color: #2c3e50;
            --border-color: #e2e8f0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: var(--background-color);
            color: var(--text-color);
        }

        header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            margin: 0;
            padding: 0 2rem;
            font-size: 1.5rem;
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-evenly;
            padding: 0.5rem 2rem;
            gap: 1rem;
            list-style: none;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .admin-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .admin-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .admin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
        }

        .book-list {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }

        .book-list h2 {
            margin-top: 0;
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th {
            background: #f8fafc;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--primary-color);
            border-bottom: 2px solid var(--border-color);
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        tr:hover {
            background: #f8fafc;
        }

        .delete-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            background: #c0392b;
        }

        .edit-btn {
            background: var(--primary-color);
            margin-right: 0.5rem;
        }

        .inline-form {
            display: inline-block;
        }

        .action-cell {
            display: flex;
            gap: 0.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .admin-controls {
                flex-direction: column;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }

        .nav-user {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-left: auto;
    padding: 0 1rem;
}

.nav-user-email {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
}

.btn-auth {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-auth:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
}

.btn-logout {
    background: #e74c3c;
    border: none;
}

.btn-logout:hover {
    background: #c0392b;
}

.edit-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1rem;
        }

        .save-btn {
            background: var(--success-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .save-btn:hover {
            background: #27ae60;
        }
    </style>
</head>
<body>
    <header>
        <h1>Book Store Administration</h1>
        <nav>
            <ul class="nav">
                <li class="nav-item"><a class="nav-link" href="books_dashboard.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                <?php if(isset($_SESSION['userID'])): ?>
                    <div class="nav-user">
                        <span class="nav-user-email"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                        <a href="logout.php" class="btn-auth btn-logout">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn-auth">Login</a>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>
        <div class="admin-controls">
            <a href="add_author.php" class="admin-btn">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Author
            </a>
            <a href="add_publisher.php" class="admin-btn">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Publisher
            </a>
            <a href="add_book.php" class="admin-btn">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Book
            </a>
        </div>

        <section class="book-list">
            <h2>Books List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ISBN</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Publisher</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr data-isbn="<?= htmlspecialchars($book['ISBN']) ?>">
                            <td class="isbn-cell"><?= htmlspecialchars($book['ISBN']) ?></td>
                            <td class="title-cell"><?= htmlspecialchars($book['Title']) ?></td>
                            <td class="author-cell"><?= htmlspecialchars($book['AuthorFirstName'] . ' ' . $book['AuthorLastName']) ?></td>
                            <td class="publisher-cell"><?= htmlspecialchars($book['Publisher']) ?></td>
                            <td class="price-cell">$<?= number_format($book['Retail'], 2) ?></td>
                            <td class="category-cell"><?= htmlspecialchars($book['Category']) ?></td>
                            <td class="action-cell">
                                <button class="admin-btn edit-btn" onclick="toggleEditMode(this)">Edit</button>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="isbn" value="<?= htmlspecialchars($book['ISBN']) ?>">
                                    <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this book?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        function toggleEditMode(button) {
            const row = button.closest('tr');
            const isEditing = row.classList.contains('editing');
            
            if (!isEditing) {
                // Switch to edit mode
                row.classList.add('editing');
                
                // Convert cells to input fields
                const titleCell = row.querySelector('.title-cell');
                const priceCell = row.querySelector('.price-cell');
                const categoryCell = row.querySelector('.category-cell');
                
                titleCell.innerHTML = `<input type="text" class="edit-input" value="${titleCell.textContent}" />`;
                priceCell.innerHTML = `<input type="number" step="0.01" class="edit-input" value="${priceCell.textContent.replace('$', '')}" />`;
                categoryCell.innerHTML = `<input type="text" class="edit-input" value="${categoryCell.textContent}" />`;
                
                // Change edit button to save button
                button.textContent = 'Save';
                button.classList.remove('edit-btn');
                button.classList.add('save-btn');
            } else {
                // Save changes
                const isbn = row.dataset.isbn;
                const title = row.querySelector('.title-cell input').value;
                const price = row.querySelector('.price-cell input').value;
                const category = row.querySelector('.category-cell input').value;
                
                // Get additional data from data attributes
                const pubId = row.dataset.pubid;
                const pubDate = row.dataset.pubdate;
                const cost = row.dataset.cost;
                const discount = row.dataset.discount;
                
                // Create form data with all necessary fields
                const formData = new FormData();
                formData.append('action', 'edit');
                formData.append('isbn', isbn);
                formData.append('title', title);
                formData.append('retail', price);
                formData.append('category', category);
                formData.append('pubID', pubId);
                formData.append('pubDate', pubDate);
                formData.append('cost', cost);
                formData.append('discount', discount);
                
                // Send update request
                fetch('books_dashboard.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        // Update the display
                        row.querySelector('.title-cell').textContent = title;
                        row.querySelector('.price-cell').textContent = `$${parseFloat(price).toFixed(2)}`;
                        row.querySelector('.category-cell').textContent = category;
                        
                        // Reset button
                        button.textContent = 'Edit';
                        button.classList.remove('save-btn');
                        button.classList.add('edit-btn');
                        row.classList.remove('editing');
                    } else {
                        throw new Error('Update failed');
                    }
                })
                .catch(error => {
                    alert('Error updating book: ' + error.message);
                });
            }
        }
    </script>
</body>
</html>