<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\SlaSetting;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketReply;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Departments
        $deptIT = Department::create([
            'name' => 'IT Support',
            'description' => 'Handling hardware, software, internal network, and IT credentials.',
            'is_active' => true,
        ]);

        $deptGA = Department::create([
            'name' => 'General Affairs',
            'description' => 'Managing office logistics, supplies, and physical desk setups.',
            'is_active' => true,
        ]);

        $deptFinance = Department::create([
            'name' => 'Finance & Accounting',
            'description' => 'Handling internal disbursements, tax requests, and financial verification.',
            'is_active' => true,
        ]);

        $deptHR = Department::create([
            'name' => 'Human Resources',
            'description' => 'Employee operations, onboarding requests, insurance, and leaves.',
            'is_active' => true,
        ]);

        $deptFacility = Department::create([
            'name' => 'Facility Management',
            'description' => 'Building maintenance, electrical systems, air conditioning, and safety.',
            'is_active' => true,
        ]);

        // 2. Seed Categories
        $catHardware = Category::create([
            'department_id' => $deptIT->id,
            'name' => 'Hardware & Peripheral',
            'description' => 'Monitors, laptops, mice, keyboards, docking stations, and printers.',
            'is_active' => true,
        ]);

        $catSoftware = Category::create([
            'department_id' => $deptIT->id,
            'name' => 'Software & OS Licensing',
            'description' => 'Windows OS, Office 365, development IDEs, and business suites.',
            'is_active' => true,
        ]);

        $catNetwork = Category::create([
            'department_id' => $deptIT->id,
            'name' => 'Network & Connectivity',
            'description' => 'Office Wi-Fi, LAN wall sockets, VPN tunneling, and internet bandwidth.',
            'is_active' => true,
        ]);

        $catAccount = Category::create([
            'department_id' => $deptIT->id,
            'name' => 'Account & Access Rights',
            'description' => 'Active directory, password resets, ERP credentials, and shared folders.',
            'is_active' => true,
        ]);

        $catSupplies = Category::create([
            'department_id' => $deptGA->id,
            'name' => 'Office Stationery & Supplies',
            'description' => 'Pens, printing paper, toner, and pantry necessities.',
            'is_active' => true,
        ]);

        $catFurniture = Category::create([
            'department_id' => $deptGA->id,
            'name' => 'Furniture & Ergonomics',
            'description' => 'Office chairs, height-adjustable tables, and storage cabinets.',
            'is_active' => true,
        ]);

        $catAC = Category::create([
            'department_id' => $deptFacility->id,
            'name' => 'AC & Climate Control',
            'description' => 'Air conditioning units, cooling issues, and server room climate.',
            'is_active' => true,
        ]);

        $catElectrical = Category::create([
            'department_id' => $deptFacility->id,
            'name' => 'Electrical & Lighting',
            'description' => 'Power sockets, fluorescent bulbs, UPS, and emergency lighting.',
            'is_active' => true,
        ]);

        // 3. Seed SLA Settings
        SlaSetting::create([
            'priority' => Ticket::PRIORITY_URGENT,
            'first_response_hours' => 1,
            'resolution_hours' => 8,
        ]);

        SlaSetting::create([
            'priority' => Ticket::PRIORITY_HIGH,
            'first_response_hours' => 4,
            'resolution_hours' => 24,
        ]);

        SlaSetting::create([
            'priority' => Ticket::PRIORITY_MEDIUM,
            'first_response_hours' => 8,
            'resolution_hours' => 48,
        ]);

        SlaSetting::create([
            'priority' => Ticket::PRIORITY_LOW,
            'first_response_hours' => 24,
            'resolution_hours' => 72,
        ]);

        // 4. Seed Users
        $admin = User::create([
            'name' => 'Administrator System',
            'email' => 'admin@helpdesk.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'job_title' => 'Head of Infrastructure & Operations',
            'phone' => '+62 811-0001-001',
            'is_active' => true,
        ]);

        $supervisor = User::create([
            'name' => 'Alex Pratama',
            'email' => 'supervisor@helpdesk.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPERVISOR,
            'department_id' => $deptIT->id,
            'job_title' => 'IT Operations Supervisor',
            'phone' => '+62 811-0001-002',
            'is_active' => true,
        ]);

        $agentIT1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'agent.it@helpdesk.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_AGENT,
            'department_id' => $deptIT->id,
            'job_title' => 'Senior Support Specialist',
            'phone' => '+62 811-0001-003',
            'is_active' => true,
        ]);

        $agentIT2 = User::create([
            'name' => 'Citra Lestari',
            'email' => 'citra.it@helpdesk.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_AGENT,
            'department_id' => $deptIT->id,
            'job_title' => 'Network & Systems Specialist',
            'phone' => '+62 811-0001-004',
            'is_active' => true,
        ]);

        $agentGA = User::create([
            'name' => 'Doni Wijaya',
            'email' => 'agent.ga@helpdesk.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_AGENT,
            'department_id' => $deptGA->id,
            'job_title' => 'GA & Facilities Coordinator',
            'phone' => '+62 811-0001-005',
            'is_active' => true,
        ]);

        $userFinance = User::create([
            'name' => 'Sarah Jenkins',
            'email' => 'user@helpdesk.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_USER,
            'department_id' => $deptFinance->id,
            'job_title' => 'Senior Financial Analyst',
            'phone' => '+62 812-3456-789',
            'is_active' => true,
        ]);

        $userHR = User::create([
            'name' => 'Kevin Hartanto',
            'email' => 'kevin@helpdesk.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_USER,
            'department_id' => $deptHR->id,
            'job_title' => 'People Operations Specialist',
            'phone' => '+62 813-9876-543',
            'is_active' => true,
        ]);

        $userFinance2 = User::create([
            'name' => 'Maya Putri',
            'email' => 'maya@helpdesk.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_USER,
            'department_id' => $deptFinance->id,
            'job_title' => 'Accounts Payable Lead',
            'phone' => '+62 814-2233-445',
            'is_active' => true,
        ]);

        // 5. Seed Sample Tickets
        $year = Carbon::now()->format('Y');

        // Ticket 1: In Progress with replies and internal note
        $t1Opened = Carbon::now()->subHours(6);
        $t1 = Ticket::create([
            'ticket_number' => "HD-{$year}-000101",
            'user_id' => $userFinance->id,
            'department_id' => $deptIT->id,
            'category_id' => $catHardware->id,
            'assigned_to' => $agentIT1->id,
            'subject' => 'Printer Finance Lantai 2 Macet & Paper Jam',
            'description' => "Printer HP LaserJet di divisi Finance Lantai 2 mengalami macet saat mencetak berkas laporan audit bulanan. Kertas tersangkut di bagian penarik belakang dan lampu indikator berkedip merah.\n\nMohon bantuannya segera karena laporan harus ditandatangani hari ini.",
            'priority' => Ticket::PRIORITY_HIGH,
            'status' => Ticket::STATUS_IN_PROGRESS,
            'opened_at' => $t1Opened,
            'first_replied_at' => $t1Opened->copy()->addMinutes(25),
            'sla_due_at' => $t1Opened->copy()->addHours(24),
            'created_at' => $t1Opened,
            'updated_at' => Carbon::now()->subHours(2),
        ]);

        TicketActivity::create([
            'ticket_id' => $t1->id,
            'user_id' => $userFinance->id,
            'activity_type' => 'created',
            'description' => "Ticket created by {$userFinance->name}",
            'new_value' => $t1->ticket_number,
            'created_at' => $t1Opened,
        ]);

        TicketActivity::create([
            'ticket_id' => $t1->id,
            'user_id' => $supervisor->id,
            'activity_type' => 'assigned',
            'description' => "Ticket assigned to {$agentIT1->name} by {$supervisor->name}",
            'old_value' => 'Unassigned',
            'new_value' => $agentIT1->name,
            'created_at' => $t1Opened->copy()->addMinutes(15),
        ]);

        TicketReply::create([
            'ticket_id' => $t1->id,
            'user_id' => $agentIT1->id,
            'message' => "Halo Bu Sarah, tiket sudah kami terima. Saya sedang membawa toolkit dan roller pembersih cadangan untuk mengecek printer di Lantai 2.",
            'is_internal' => false,
            'created_at' => $t1Opened->copy()->addMinutes(25),
        ]);

        TicketReply::create([
            'ticket_id' => $t1->id,
            'user_id' => $agentIT1->id,
            'message' => "Catatan Internal: Roller pick-up printer sudah mulai aus. Sementara sudah dibersihkan dengan alkohol isopropil, tetapi perlu dipesan sparepart roller kit baru minggu depan.",
            'is_internal' => true,
            'created_at' => $t1Opened->copy()->addMinutes(45),
        ]);

        TicketReply::create([
            'ticket_id' => $t1->id,
            'user_id' => $userFinance->id,
            'message' => "Terima kasih Pak Budi. Kertas yang tersangkut sudah kami biarkan agar tidak merusak gear di dalamnya.",
            'is_internal' => false,
            'created_at' => $t1Opened->copy()->addMinutes(50),
        ]);

        // Ticket 2: Urgent, Open, Unassigned (Near Deadline)
        $t2Opened = Carbon::now()->subMinutes(50);
        $t2 = Ticket::create([
            'ticket_number' => "HD-{$year}-000102",
            'user_id' => $userFinance2->id,
            'department_id' => $deptIT->id,
            'category_id' => $catNetwork->id,
            'assigned_to' => null,
            'subject' => 'Koneksi VPN Kantor Putus Saat Akses Server Database',
            'description' => "Koneksi VPN client tiba-tiba disconnect dan gagal menyambung kembali dengan pesan kesalahan 'TLS Handshake Failed'. Saya sedang memproses transfer pembayaran supplier sore ini.",
            'priority' => Ticket::PRIORITY_URGENT,
            'status' => Ticket::STATUS_OPEN,
            'opened_at' => $t2Opened,
            'sla_due_at' => $t2Opened->copy()->addHours(8),
            'created_at' => $t2Opened,
            'updated_at' => $t2Opened,
        ]);

        TicketActivity::create([
            'ticket_id' => $t2->id,
            'user_id' => $userFinance2->id,
            'activity_type' => 'created',
            'description' => "Ticket created by {$userFinance2->name}",
            'new_value' => $t2->ticket_number,
            'created_at' => $t2Opened,
        ]);

        // Ticket 3: Resolved ticket
        $t3Opened = Carbon::now()->subDays(2);
        $t3Resolved = Carbon::now()->subHours(10);
        $t3 = Ticket::create([
            'ticket_number' => "HD-{$year}-000103",
            'user_id' => $userHR->id,
            'department_id' => $deptIT->id,
            'category_id' => $catAccount->id,
            'assigned_to' => $agentIT2->id,
            'subject' => 'Permintaan Pembuatan Akun Email & ERP untuk Onboarding Karyawan Baru',
            'description' => "Mohon disiapkan akun Google Workspace dan akses ke modul HRIS untuk karyawan baru kita yang akan bergabung hari Senin: Ahmad Rizki (rizki.ahmad@company.test).",
            'priority' => Ticket::PRIORITY_MEDIUM,
            'status' => Ticket::STATUS_RESOLVED,
            'opened_at' => $t3Opened,
            'first_replied_at' => $t3Opened->copy()->addHour(),
            'resolved_at' => $t3Resolved,
            'sla_due_at' => $t3Opened->copy()->addHours(48),
            'created_at' => $t3Opened,
            'updated_at' => $t3Resolved,
        ]);

        TicketActivity::create([
            'ticket_id' => $t3->id,
            'user_id' => $userHR->id,
            'activity_type' => 'created',
            'description' => "Ticket created by {$userHR->name}",
            'new_value' => $t3->ticket_number,
            'created_at' => $t3Opened,
        ]);

        TicketActivity::create([
            'ticket_id' => $t3->id,
            'user_id' => $agentIT2->id,
            'activity_type' => 'resolved',
            'description' => "Ticket marked as Resolved by {$agentIT2->name}",
            'old_value' => 'in_progress',
            'new_value' => 'resolved',
            'created_at' => $t3Resolved,
        ]);

        TicketReply::create([
            'ticket_id' => $t3->id,
            'user_id' => $agentIT2->id,
            'message' => "Akun email rizki.ahmad@company.test dan permission modul HRIS telah aktif. Kredensial sementara sudah dikirimkan via email terenkripsi ke Bu Kevin.",
            'is_internal' => false,
            'created_at' => $t3Resolved,
        ]);

        // Ticket 4: General Affairs Furniture Request (Open)
        $t4Opened = Carbon::now()->subHours(12);
        $t4 = Ticket::create([
            'ticket_number' => "HD-{$year}-000104",
            'user_id' => $userFinance->id,
            'department_id' => $deptGA->id,
            'category_id' => $catFurniture->id,
            'assigned_to' => $agentGA->id,
            'subject' => 'Penggantian Hidrolik Kursi Kerja Rusak di Meja Finance 04',
            'description' => "Kursi ergonomis di meja saya selalu turun otomatis (hidrolik bocor). Menyebabkan posisi mengetik tidak nyaman.",
            'priority' => Ticket::PRIORITY_LOW,
            'status' => Ticket::STATUS_OPEN,
            'opened_at' => $t4Opened,
            'sla_due_at' => $t4Opened->copy()->addHours(72),
            'created_at' => $t4Opened,
            'updated_at' => $t4Opened,
        ]);

        TicketActivity::create([
            'ticket_id' => $t4->id,
            'user_id' => $userFinance->id,
            'activity_type' => 'created',
            'description' => "Ticket created by {$userFinance->name}",
            'new_value' => $t4->ticket_number,
            'created_at' => $t4Opened,
        ]);

        // Ticket 5: Facility Management Overdue Ticket
        $t5Opened = Carbon::now()->subHours(36);
        $t5 = Ticket::create([
            'ticket_number' => "HD-{$year}-000105",
            'user_id' => $supervisor->id,
            'department_id' => $deptFacility->id,
            'category_id' => $catAC->id,
            'assigned_to' => $agentGA->id,
            'subject' => 'AC Ruang Server IT Tidak Dingin (Suhu Mencapai 27.5C)',
            'description' => "Suhu di ruang server IT Gedung Utama terpantau naik ke 27.5C (ambang batas normal 20C). Alarm sensor suhu berbunyi.",
            'priority' => Ticket::PRIORITY_URGENT,
            'status' => Ticket::STATUS_IN_PROGRESS,
            'opened_at' => $t5Opened,
            'first_replied_at' => $t5Opened->copy()->addMinutes(20),
            'sla_due_at' => $t5Opened->copy()->addHours(8), // Overdue!
            'created_at' => $t5Opened,
            'updated_at' => Carbon::now()->subHours(1),
        ]);

        TicketActivity::create([
            'ticket_id' => $t5->id,
            'user_id' => $supervisor->id,
            'activity_type' => 'created',
            'description' => "Ticket created by {$supervisor->name}",
            'new_value' => $t5->ticket_number,
            'created_at' => $t5Opened,
        ]);

        TicketReply::create([
            'ticket_id' => $t5->id,
            'user_id' => $agentGA->id,
            'message' => "Teknisi AC Daikin eksternal sudah dihubungi dan sedang menuju lokasi untuk pengisian freon R32 dan cek kompresor.",
            'is_internal' => false,
            'created_at' => $t5Opened->copy()->addMinutes(20),
        ]);

        // Ticket 6: Closed ticket
        $t6Opened = Carbon::now()->subDays(5);
        $t6Resolved = Carbon::now()->subDays(4);
        $t6 = Ticket::create([
            'ticket_number' => "HD-{$year}-000106",
            'user_id' => $userFinance2->id,
            'department_id' => $deptIT->id,
            'category_id' => $catSoftware->id,
            'assigned_to' => $agentIT1->id,
            'subject' => 'Aktivasi Lisensi Microsoft Excel 365 Expired',
            'description' => "Muncul popup 'Product Deactivated' saat membuka Excel. Mohon aktivasi ulang lisensi Microsoft 365.",
            'priority' => Ticket::PRIORITY_MEDIUM,
            'status' => Ticket::STATUS_CLOSED,
            'opened_at' => $t6Opened,
            'first_replied_at' => $t6Opened->copy()->addMinutes(30),
            'resolved_at' => $t6Resolved,
            'closed_at' => Carbon::now()->subDays(3),
            'sla_due_at' => $t6Opened->copy()->addHours(48),
            'created_at' => $t6Opened,
            'updated_at' => Carbon::now()->subDays(3),
        ]);

        // 6. Seed Notification Settings
        $events = [
            'ticket_created',
            'ticket_assigned',
            'reply_added',
            'status_changed',
            'priority_changed',
        ];

        foreach ($events as $event) {
            NotificationSetting::create([
                'event' => $event,
                'web_enabled' => true,
                'push_enabled' => true,
                'whatsapp_enabled' => true,
            ]);
        }

        // 7. Seed Sample In-App Notifications
        Notification::create([
            'user_id' => $agentIT1->id,
            'type' => Notification::TYPE_TICKET_ASSIGNED,
            'title' => 'New Ticket Assigned: #' . $t1->ticket_number,
            'message' => "Ticket '{$t1->subject}' has been assigned to you by {$supervisor->name}.",
            'url' => route('tickets.show', $t1),
            'read_at' => null,
            'created_at' => Carbon::now()->subMinutes(40),
        ]);

        Notification::create([
            'user_id' => $userFinance->id,
            'type' => Notification::TYPE_REPLY_ADDED,
            'title' => 'Reply on #' . $t1->ticket_number,
            'message' => "{$agentIT1->name} replied: \"Halo Bu Sarah, tiket sudah kami terima...\"",
            'url' => route('tickets.show', $t1),
            'read_at' => null,
            'created_at' => Carbon::now()->subMinutes(25),
        ]);

        Notification::create([
            'user_id' => $admin->id,
            'type' => Notification::TYPE_STATUS_CHANGED,
            'title' => 'Ticket Resolved: #' . $t3->ticket_number,
            'message' => "Ticket '{$t3->subject}' marked as Resolved by {$agentIT2->name}.",
            'url' => route('tickets.show', $t3),
            'read_at' => null,
            'created_at' => Carbon::now()->subHours(10),
        ]);
    }
}
