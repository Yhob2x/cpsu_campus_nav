<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Directory - CPSU Navigator</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            -webkit-tap-highlight-color: transparent;
        }

        :root {
            --primary: #0284c7;
            --accent: #16a34a;
            --warning: #f59e0b;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            background: #f8fafc;
        }

        .modern-header {
            background: linear-gradient(135deg, var(--primary) 0%, #0369a1 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: 16px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header-content {
            max-width: 7xl;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .back-link {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-4px);
        }

        .container-main {
            max-width: 7xl;
            margin: 0 auto;
            padding: 32px 16px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: #64748b;
            margin-bottom: 32px;
        }

        .filter-bar {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #64748b;
        }

        .filter-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .filter-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .office-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .office-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-top: 4px solid var(--primary);
            display: flex;
            flex-direction: column;
        }

        .office-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .office-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary) 0%, #0369a1 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .card-info {
            flex: 1;
        }

        .office-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .office-building {
            font-size: 0.85rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .card-details {
            padding: 0 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }

        .detail-item {
            font-size: 0.85rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-icon {
            color: var(--primary);
            width: 16px;
            text-align: center;
        }

        .category-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 8px;
            width: fit-content;
        }

        .badge-admin {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-academic {
            background: #dbeafe;
            color: #0c4a6e;
        }

        .badge-facility {
            background: #dcfce7;
            color: #166534;
        }

        .card-footer {
            padding: 16px 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 8px;
        }

        .navigate-btn {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, #0369a1 100%);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-align: center;
        }

        .navigate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.3);
        }

        .navigate-btn:active {
            transform: scale(0.98);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 16px;
        }

        .empty-text {
            font-size: 1.2rem;
            color: #64748b;
            margin-bottom: 8px;
        }

        .empty-subtext {
            color: #94a3b8;
        }

        @media (max-width: 768px) {
            .office-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .header-content {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

            .back-link {
                width: 100%;
                justify-content: center;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .office-card {
            animation: fadeIn 0.4s ease-out;
        }
    </style>
</head>
<body>
    <!-- Modern Header -->
    <header class="modern-header">
        <div class="header-content">
            <h1 class="header-title">
                <i class="fas fa-building"></i> Office Directory
            </h1>
            <a href="{{ url('/') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Map
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container-main">
        <h2 class="page-title">Find Your Office</h2>
        <p class="page-subtitle">Browse all departments and facilities across the campus</p>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <button class="filter-btn active" onclick="filterByCategory('all')">
                <i class="fas fa-check mr-2"></i> All Offices
            </button>
            <button class="filter-btn" onclick="filterByCategory('Administrative')">
                <i class="fas fa-building mr-2"></i> Administrative
            </button>
            <button class="filter-btn" onclick="filterByCategory('Academic')">
                <i class="fas fa-graduation-cap mr-2"></i> Academic
            </button>
            <button class="filter-btn" onclick="filterByCategory('Facility')">
                <i class="fas fa-tools mr-2"></i> Facility
            </button>
        </div>

        <!-- Office Grid -->
        <div class="office-grid" id="officeGrid">
            @php
                $offices = App\Models\Office::all();
            @endphp
            @forelse($offices as $office)
            <div class="office-card" data-category="{{ $office->category }}">
                <div class="card-header">
                    <div class="office-icon">
                        <i class="fas fa-map-location-dot"></i>
                    </div>
                    <div class="card-info">
                        <div class="office-name">{{ $office->name }}</div>
                        <div class="office-building">
                            <i class="fas fa-building" style="font-size: 0.75rem;"></i>
                            {{ $office->building }}
                        </div>
                    </div>
                </div>
                <div class="card-details">
                    @if($office->room_number)
                    <div class="detail-item">
                        <span class="detail-icon"><i class="fas fa-door-open"></i></span>
                        <span>Room {{ $office->room_number }}</span>
                    </div>
                    @endif
                    @if($office->working_hours)
                    <div class="detail-item">
                        <span class="detail-icon"><i class="fas fa-clock"></i></span>
                        <span>{{ $office->working_hours }}</span>
                    </div>
                    @endif
                    <span class="category-badge badge-{{ strtolower($office->category) }}">
                        {{ $office->category }}
                    </span>
                </div>
                <div class="card-footer">
                    <a href="{{ url('/?office=' . $office->office_id) }}" class="navigate-btn">
                        <i class="fas fa-directions"></i> Navigate
                    </a>
                </div>
            </div>
            @empty
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="empty-icon">📭</div>
                <div class="empty-text">No Offices Found</div>
                <div class="empty-subtext">Please check back later or contact administration</div>
            </div>
            @endforelse
        </div>
    </div>

    <script>
        function filterByCategory(category) {
            const cards = document.querySelectorAll('.office-card');
            const buttons = document.querySelectorAll('.filter-btn');

            // Update button states
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.closest('.filter-btn').classList.add('active');

            // Filter cards
            cards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                    setTimeout(() => card.style.opacity = '1', 0);
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>