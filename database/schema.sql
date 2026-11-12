PRAGMA foreign_keys = ON;

CREATE TABLE users (
    UserID INTEGER PRIMARY KEY AUTOINCREMENT,
    Username TEXT NOT NULL UNIQUE,
    Email TEXT NOT NULL UNIQUE,
    PasswordHash TEXT NOT NULL,
    FirstName TEXT NOT NULL,
    LastName TEXT NOT NULL,
    ContactInformation TEXT,
    Role TEXT NOT NULL DEFAULT 'Fan'
        CHECK (Role IN ('Fan', 'Admin')),
    AccountStatus TEXT NOT NULL DEFAULT 'Active'
        CHECK (AccountStatus IN ('Active', 'Inactive')),
    CreatedAt TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE matches (
    MatchID INTEGER PRIMARY KEY AUTOINCREMENT,
    Opponent TEXT NOT NULL,
    Date TEXT NOT NULL,
    Time TEXT NOT NULL,
    MatchType TEXT NOT NULL
        CHECK (MatchType IN ('League', 'Cup')),
    MatchStatus TEXT NOT NULL DEFAULT 'Scheduled'
        CHECK (MatchStatus IN ('Scheduled', 'Completed', 'Cancelled')),
    CreatedAt TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE seats (
    SeatID INTEGER PRIMARY KEY AUTOINCREMENT,
    Section TEXT NOT NULL,
    Row INTEGER NOT NULL CHECK (Row > 0),
    Number INTEGER NOT NULL CHECK (Number > 0),
    Price REAL NOT NULL CHECK (Price >= 0),
    SeatStatus TEXT NOT NULL DEFAULT 'Active'
        CHECK (SeatStatus IN ('Active', 'Inactive')),
    CreatedAt TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (Section, Row, Number)
);
CREATE TABLE tickets (
    TicketID INTEGER PRIMARY KEY AUTOINCREMENT,
    UserID INTEGER NOT NULL,
    MatchID INTEGER NOT NULL,
    SeatID INTEGER NOT NULL,
    TicketStatus TEXT NOT NULL DEFAULT 'Pending'
        CHECK (TicketStatus IN
            ('Pending', 'Purchased', 'Cancelled', 'Expired')),
    ReservedAt TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ExpiresAt TEXT,
    TicketPrice REAL NOT NULL CHECK (TicketPrice >= 0),
    CreatedAt TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES users(UserID),
    FOREIGN KEY (MatchID) REFERENCES matches(MatchID),
    FOREIGN KEY (SeatID) REFERENCES seats(SeatID)
);
CREATE TABLE transactions (
    TransactionID INTEGER PRIMARY KEY AUTOINCREMENT,
    UserID INTEGER NOT NULL,
    TicketID INTEGER NOT NULL,
    Amount REAL NOT NULL CHECK (Amount >= 0),
    TransactionDate TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PaymentMethod TEXT NOT NULL DEFAULT 'Simulated Payment'
        CHECK (PaymentMethod = 'Simulated Payment'),
    TransactionStatus TEXT NOT NULL
        CHECK (TransactionStatus IN
            ('Completed', 'Failed', 'Cancelled', 'Voided')),
    CreatedAt TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DeletedAt TEXT,
    FOREIGN KEY (UserID) REFERENCES users(UserID),
    FOREIGN KEY (TicketID) REFERENCES tickets(TicketID)
);
CREATE UNIQUE INDEX idx_one_completed_transaction
ON transactions(TicketID)
WHERE TransactionStatus = 'Completed';
CREATE UNIQUE INDEX idx_one_active_ticket_per_seat
ON tickets(MatchID, SeatID)
WHERE TicketStatus IN ('Pending', 'Purchased');
