<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
use HasFactory;

protected $table = 'notifications';

protected $fillable = [
'notification_master_id',
'for_user_id',
'for_building_type',
'for_user_type',
'variable_data',
'is_read',
];

protected $casts = [
'variable_data' => 'array', // Automatically converts JSON to array
];

public function master()
{
return $this->belongsTo(NotificationMaster::class, 'notification_master_id');
}

public function getNotificationMessage()
{
$notificationTemplate = $this->master;

if (!$notificationTemplate) {
return null;
}

$message = $notificationTemplate->message_template;
$variableData = $this->variable_data ?? []; // Ensure it's always an array

foreach ($variableData as $key => $value) {
$message = str_replace('{$' . $key . '}', $value, $message);
}

return $message;
}
}