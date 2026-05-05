<?php
$modelsDir = __DIR__ . '/app/Models/';

$models = [
    'User' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee() { return $this->hasOne(Employee::class); }
    public function complaints() { return $this->hasMany(Complaint::class); }
    public function activityLogs() { return $this->hasMany(ActivityLog::class); }

    public function isAdmin() { return $this->role === 'admin'; }
    public function isHr() { return $this->role === 'hr'; }
    public function isEmployee() { return $this->role === 'employee'; }
}
EOT,
    'Department' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function employees() { return $this->hasMany(Employee::class); }
}
EOT,
    'Employee' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'department_id', 'employee_code', 'phone', 'address', 'joined_date'];

    public function user() { return $this->belongsTo(User::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function attendances() { return $this->hasMany(Attendance::class); }
    public function leaves() { return $this->hasMany(Leave::class); }
    public function payrolls() { return $this->hasMany(Payroll::class); }
    public function contracts() { return $this->hasMany(Contract::class); }
    public function alerts() { return $this->hasMany(Alert::class); }
}
EOT,
    'Attendance' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'date', 'check_in', 'check_out', 'total_hours', 'status'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
EOT,
    'Leave' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'start_date', 'end_date', 'type', 'reason', 'status', 'admin_remark'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
EOT,
    'Complaint' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'description', 'is_anonymous', 'status', 'admin_response'];

    public function user() { return $this->belongsTo(User::class); }
}
EOT,
    'Alert' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'type', 'message', 'is_read'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
EOT,
    'Payroll' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'month', 'year', 'basic_salary', 'overtime_hours', 'overtime_pay', 'deductions', 'net_salary', 'status'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
EOT,
    'Contract' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'start_date', 'end_date', 'document_path', 'status'];

    public function employee() { return $this->belongsTo(Employee::class); }
}
EOT,
    'ActivityLog' => <<<'EOT'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'action', 'description'];

    public function user() { return $this->belongsTo(User::class); }
}
EOT,
];

foreach ($models as $name => $content) {
    file_put_contents($modelsDir . $name . '.php', $content);
}
echo "Models updated successfully.";
