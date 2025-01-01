<html lang="en"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Store</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }

        .card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .card img {
            height: 200px;
            object-fit: cover;
        }

        .price {
            font-size: 1.2em;
            color: #28a745;
            font-weight: bold;
        }

        .rating {
            color: #f4c430;
        }
    </style>
</head>
<body>
<header class="bg-dark text-white text-center py-3">
    <h1>Book Store</h1>
    <p>Discover and shop the best books online!</p>
</header>

<div class="container my-5">
    <div id="books-container" class="row g-4">
        <!-- Example of a book card -->
        <div class="col-md-4 col-lg-3">
            <div class="card h-100">
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="Book Title">
                <div class="card-body">
                    <h5 class="card-title">Book Title</h5>
                    <p class="card-text">By Author Name</p>
                    <p class="price">$10.99</p>
                    <p class="rating">★★★★☆</p>
                    <a href="#" class="btn btn-primary">View Details</a>
                </div>
            </div>
        </div>

        <!-- Add more cards dynamically using JavaScript -->

        <div class="col-md-4 col-lg-3">
            <div class="card h-100">
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="The Great Gatsby">
                <div class="card-body">
                    <h5 class="card-title">The Great Gatsby</h5>
                    <p class="card-text">By F. Scott Fitzgerald</p>
                    <p class="price">$12.99</p>
                    <p class="rating">★★★★★</p>
                    <a href="#" class="btn btn-primary">View Details</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-3">
            <div class="card h-100">
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="To Kill a Mockingbird">
                <div class="card-body">
                    <h5 class="card-title">To Kill a Mockingbird</h5>
                    <p class="card-text">By Harper Lee</p>
                    <p class="price">$9.99</p>
                    <p class="rating">★★★★☆</p>
                    <a href="#" class="btn btn-primary">View Details</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-3">
            <div class="card h-100">
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="1984">
                <div class="card-body">
                    <h5 class="card-title">1984</h5>
                    <p class="card-text">By George Orwell</p>
                    <p class="price">$15.99</p>
                    <p class="rating">★★★★☆</p>
                    <a href="#" class="btn btn-primary">View Details</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        fetchBooks();
    });

    async function fetchBooks() {
        const books = [
            {
                id: 1,
                title: "The Great Gatsby",
                author: "F. Scott Fitzgerald",
                price: 12.99,
                coverImage: "https://via.placeholder.com/150",
                rating: 5
            },
            {
                id: 2,
                title: "To Kill a Mockingbird",
                author: "Harper Lee",
                price: 9.99,
                coverImage: "https://via.placeholder.com/150",
                rating: 4
            },
            {
                id: 3,
                title: "1984",
                author: "George Orwell",
                price: 15.99,
                coverImage: "https://via.placeholder.com/150",
                rating: 4
            }
        ];

        displayBooks(books);
    }

    function displayBooks(books) {
        const container = document.getElementById('books-container');
        books.forEach(book => {
            const bookCard = `
                    <div class="col-md-4 col-lg-3">
                        <div class="card h-100">
                            <img src="${book.coverImage}" class="card-img-top" alt="${book.title}">
                            <div class="card-body">
                                <h5 class="card-title">${book.title}</h5>
                                <p class="card-text">By ${book.author}</p>
                                <p class="price">$${book.price.toFixed(2)}</p>
                                <p class="rating">${'★'.repeat(book.rating)}${'☆'.repeat(5 - book.rating)}</p>
                                <a href="#" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                `;
            container.insertAdjacentHTML('beforeend', bookCard);
        });
    }
</script>

<script>
    (function() {
        var ws = new WebSocket('ws://' + window.location.host +
            '/jb-server-page?reloadMode=RELOAD_ON_SAVE&'+
            'referrer=' + encodeURIComponent(window.location.pathname));
        ws.onmessage = function (msg) {
            if (msg.data === 'reload') {
                window.location.reload();
            }
            if (msg.data.startsWith('update-css ')) {
                var messageId = msg.data.substring(11);
                var links = document.getElementsByTagName('link');
                for (var i = 0; i < links.length; i++) {
                    var link = links[i];
                    if (link.rel !== 'stylesheet') continue;
                    var clonedLink = link.cloneNode(true);
                    var newHref = link.href.replace(/(&|\?)jbUpdateLinksId=\d+/, "$1jbUpdateLinksId=" + messageId);
                    if (newHref !== link.href) {
                        clonedLink.href = newHref;
                    }
                    else {
                        var indexOfQuest = newHref.indexOf('?');
                        if (indexOfQuest >= 0) {
                            // to support ?foo#hash
                            clonedLink.href = newHref.substring(0, indexOfQuest + 1) + 'jbUpdateLinksId=' + messageId + '&' +
                                newHref.substring(indexOfQuest + 1);
                        }
                        else {
                            clonedLink.href += '?' + 'jbUpdateLinksId=' + messageId;
                        }
                    }
                    link.replaceWith(clonedLink);
                }
            }
        };
    })();
</script></body></html>
