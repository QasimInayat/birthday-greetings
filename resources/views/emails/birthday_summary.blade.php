<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Birthday Summary Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            background: #ffffff;
            margin: 0 auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        .content {
            padding: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 2px solid #edf2f7;
            padding-bottom: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .section-title span {
            background: #764ba2;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            margin-left: 10px;
        }
        .birthday-item {
            display: flex;
            align-items: center;
            background: #f8fafc;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: transform 0.2s;
        }
        .avatar {
            width: 50px;
            height: 50px;
            background: #e2e8f0;
            border-radius: 50%;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #718096;
            font-size: 20px;
            overflow: hidden;
        }
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .info {
            flex: 1;
        }
        .name {
            font-weight: 600;
            color: #2d3748;
            font-size: 16px;
            margin-bottom: 2px;
        }
        .details {
            font-size: 14px;
            color: #718096;
        }
        .date {
            font-weight: 600;
            color: #764ba2;
            font-size: 14px;
        }
        .footer {
            background: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #a0aec0;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #a0aec0;
            font-style: italic;
        }
        .badge-today {
            background: #f6ad55;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Birthday Report</h1>
            <p>{{ date('l, d F Y') }}</p>
        </div>
        
        <div class="content">
            <!-- Today's Birthdays -->
            <div class="section-title">
                Today's Birthdays <span>{{ count($todaysBirthdays) }}</span>
            </div>
            
            @if(count($todaysBirthdays) > 0)
                @foreach($todaysBirthdays as $employee)
                    <div class="birthday-item">
                        <div class="avatar">
                            {{-- Mail clients cannot run an onerror fallback, so check the file on disk. --}}
                            @if($employee->profile_image && file_exists(public_path($employee->profile_image)))
                                <img src="{{ url($employee->profile_image) }}" alt="{{ $employee->full_name }}">
                            @else
                                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="info">
                            <div class="name">
                                {{ $employee->full_name }} 
                                <span class="badge-today">Today 🎂</span>
                            </div>
                            <div class="details">{{ $employee->designation }} | {{ $employee->department }}</div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="no-data">No birthdays today.</div>
            @endif

            <!-- Upcoming Birthdays (Next 7 days) -->
            <div class="section-title" style="margin-top: 40px;">
                Upcoming Birthdays <span>{{ count($upcomingBirthdays) }}</span>
            </div>
            
            @if(count($upcomingBirthdays) > 0)
                @foreach($upcomingBirthdays as $employee)
                    <div class="birthday-item">
                        <div class="avatar">
                            {{-- Mail clients cannot run an onerror fallback, so check the file on disk. --}}
                            @if($employee->profile_image && file_exists(public_path($employee->profile_image)))
                                <img src="{{ url($employee->profile_image) }}" alt="{{ $employee->full_name }}">
                            @else
                                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="info">
                            <div class="name">{{ $employee->full_name }}</div>
                            <div class="details">{{ $employee->designation }} | {{ $employee->department }}</div>
                        </div>
                        <div class="date">
                            {{ \Carbon\Carbon::parse($employee->birthday)->format('d M') }}
                        </div>
                    </div>
                @endforeach
            @else
                <div class="no-data">No upcoming birthdays in the next 7 days.</div>
            @endif
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} Birthday Manager. All rights reserved.<br>
            This is an automated report.
        </div>
    </div>
</body>
</html>
