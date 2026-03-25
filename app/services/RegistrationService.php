<?php

class RegistrationService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function getAdminIds(): array
    {
        $stmt = $this->pdo->query("SELECT id FROM admin"); // or SELECT id
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function buildFullName(string $first, string $middle, string $last): string
    {
        // Remove extra spaces and avoid double spaces if middle is empty
        return trim(
            $first . ' ' .
                ($middle ? $middle . ' ' : '') .
                $last
        );
    }

    public function register(array $data): void
    {
        // Normalize + build full name
        $data = array_map('trim', $data);

        $data['fullName'] = $this->buildFullName(
            $data['firstName'] ?? '',
            $data['middleName'] ?? '',
            $data['lastName'] ?? ''
        );

        $this->validate($data);

        $this->pdo->beginTransaction();

        try {
            $userId = $this->createUser($data);

            match ($data['role']) {
                'staff' => $this->createStaff($userId, $data),
                'student' => $this->createStudent($userId, $data),
                default => throw new Exception("INVALID_ROLE")
            };

            $this->createNotification($data['role'], $data['fullName']);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function validate(array $data): void
    {
        if (empty($data['email']) || empty($data['password'])) {
            throw new Exception("EMPTY_FIELDS");
        }

        if ($data['password'] !== $data['confirm_password']) {
            throw new Exception("PASSWORD_NOT_SAME");
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);

        if ($stmt->fetchColumn() > 0) {
            throw new Exception("EMAIL_ALREADY_EXISTS");
        }

        if ($data['role'] === 'student') {
            if (!str_ends_with($data['email'], '@addu.edu.ph')) {
                throw new Exception("EMAIL_NOT_ADDU");
            }

            if (!preg_match('/\d+/', $data['email'], $matches) || $matches[0] !== $data['student_id']) {
                throw new Exception("NOT_SAME_ID");
            }

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id = ?");
            $stmt->execute([$data['student_id']]);

            if ($stmt->fetchColumn() > 0) {
                throw new Exception("STUDENT_ID_EXISTS");
            }
        }
    }

    private function createUser(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (email, password, role, status, date_created)
            VALUES (?, ?, ?, 'active', NOW())
        ");

        $stmt->execute([
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'],
        ]);

        return $this->pdo->lastInsertId();
    }


    private function createStaff(int $userId, array $data): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO staff_accounts (user_id, first_name, middle_name, last_name, fullName, department, phone_number, gender)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $data['firstName'],
            $data['middleName'],
            $data['lastName'],
            $data['fullName'],
            $data['department'] ?? '',
            $data['phone_number'] ?? '',
            $data['gender'] ?? ''
        ]);
    }

    private function createStudent(int $userId, array $data): void
    {
        $token = bin2hex(random_bytes(25));

        $stmt = $this->pdo->prepare("
            INSERT INTO students (
                user_id, first_name, middle_name, last_name,
                student_id, gender, course, year_level, activation_token
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $data['firstName'],
            $data['middleName'],
            $data['lastName'],
            $data['student_id'],
            $data['gender'],
            $data['course'],
            $data['year_level'],
            $token
        ]);
    }

    private function createNotification(string $role, string $fullName): void
    {
        $titles = [
            'staff' => 'New Staff Account Registration',
            'student' => 'New Student Account Registration'
        ];

        $adminIds = $this->getAdminIds();

        $stmt = $this->pdo->prepare("
        INSERT INTO admin_notifications (admin_id, type, title, link, description, date, status)
        VALUES (?, ?, ?, ?, ?, NOW(), 'unread')
    ");

        foreach ($adminIds as $adminId) {
            $stmt->execute([
                $adminId,
                $role,
                $titles[$role],
                '',
                "A new $role account registered: $fullName"
            ]);
        }
    }
}
