<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property string $id
 * @property string $worker_id
 * @property string|null $location_id
 * @property string|null $worker_shift_schedule_id
 * @property string|null $business_trip_id
 * @property \Illuminate\Support\Carbon $check_in
 * @property \Illuminate\Support\Carbon|null $check_out
 * @property numeric|null $check_in_latitude
 * @property numeric|null $check_in_longitude
 * @property numeric|null $check_out_latitude
 * @property numeric|null $check_out_longitude
 * @property numeric|null $distance_from_office in meters
 * @property string|null $reason
 * @property mixed $status
 * @property mixed $absent_type
 * @property bool $present_by_admin
 * @property bool $is_late
 * @property int|null $late_minutes
 * @property bool $is_outside_radius
 * @property string|null $notes
 * @property string|null $photo_check_in
 * @property string|null $photo_check_out
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BusinessTrip|null $businessTrip
 * @property-read string $check_in_maps_url
 * @property-read string $check_out_maps_url
 * @property-read \App\Models\Location|null $location
 * @property-read \App\Models\Worker $worker
 * @property-read \App\Models\WorkerShiftSchedule|null $workerShiftSchedule
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereAbsentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereBusinessTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereCheckIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereCheckInLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereCheckInLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereCheckOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereCheckOutLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereCheckOutLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereDistanceFromOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereIsLate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereIsOutsideRadius($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereLateMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent wherePhotoCheckIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent wherePhotoCheckOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent wherePresentByAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereWorkerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Absent whereWorkerShiftScheduleId($value)
 */
	class Absent extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $worker_id
 * @property string $file_requirement_id
 * @property string $file_name
 * @property string $file_path
 * @property string $file_type
 * @property int $file_size in bytes
 * @property string $status
 * @property string|null $verified_by
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property string|null $rejection_reason
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $expired_date
 * @property bool $is_expired
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $file_size_in_kb
 * @property-read float $file_size_in_mb
 * @property-read \App\Models\User|null $verifier
 * @property-read \App\Models\Worker $worker
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereExpiredDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereFileRequirementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereIsExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereVerifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Berkas whereWorkerId($value)
 */
	class Berkas extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $worker_id
 * @property string $title
 * @property string $destination
 * @property string $destination_address
 * @property numeric|null $destination_latitude
 * @property numeric|null $destination_longitude
 * @property string $purpose
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property int $total_days
 * @property numeric|null $budget
 * @property numeric|null $actual_cost
 * @property string $transport_type
 * @property string $status
 * @property string|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $notes
 * @property string|null $attachment_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Absent> $absents
 * @property-read int|null $absents_count
 * @property-read \App\Models\User|null $approver
 * @property-read string $destination_maps_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BusinessTripReport> $reports
 * @property-read int|null $reports_count
 * @property-read \App\Models\Worker $worker
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereActualCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereAttachmentUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereDestinationAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereDestinationLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereDestinationLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereTotalDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereTransportType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTrip whereWorkerId($value)
 */
	class BusinessTrip extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $business_trip_id
 * @property string $report_title
 * @property string $report_content
 * @property string|null $attachment_url
 * @property \Illuminate\Support\Carbon $submitted_at
 * @property string $status
 * @property string|null $reviewed_by
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property string|null $review_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BusinessTrip $businessTrip
 * @property-read \App\Models\User|null $reviewer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport whereAttachmentUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport whereBusinessTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport whereReportContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport whereReportTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport whereReviewNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessTripReport whereUpdatedAt($value)
 */
	class BusinessTripReport extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property string|null $description
 * @property string $file_format Allowed file formats
 * @property int $max_file_size in KB
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read array $allowed_formats
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereFileFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereMaxFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereUpdatedAt($value)
 */
	class DocumentType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string|null $position_id
 * @property string $document_type_id
 * @property bool $is_required
 * @property bool $is_active
 * @property int $sort_order
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Berkas> $berkas
 * @property-read int|null $berkas_count
 * @property-read \App\Models\DocumentType $documentType
 * @property-read \App\Models\Position|null $position
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileRequirment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileRequirment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileRequirment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileRequirment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileRequirment whereDocumentTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileRequirment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileRequirment whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileRequirment whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileRequirment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileRequirment wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileRequirment whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileRequirment whereUpdatedAt($value)
 */
	class FileRequirment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Worker> $workers
 * @property-read int|null $workers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender whereUpdatedAt($value)
 */
	class Gender extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $worker_id
 * @property mixed $leave_type
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property int $total_days
 * @property string $reason
 * @property string|null $attachment_url
 * @property string $status
 * @property string|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approver
 * @property-read \App\Models\Worker $worker
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereAttachmentUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereLeaveType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereTotalDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereWorkerId($value)
 */
	class LeaveRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property string $address
 * @property numeric $latitude
 * @property numeric $longitude
 * @property int $radius in meters
 * @property bool $enforce_geofence
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Absent> $absents
 * @property-read int|null $absents_count
 * @property-read string $google_maps_url
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereEnforceGeofence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereRadius($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereUpdatedAt($value)
 */
	class Location extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $worker_id
 * @property \Illuminate\Support\Carbon $overtime_date
 * @property string $start_time
 * @property string $end_time
 * @property int $total_hours
 * @property string $reason
 * @property string $status
 * @property string|null $approved_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approver
 * @property-read \App\Models\Worker $worker
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereOvertimeDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereTotalHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereWorkerId($value)
 */
	class Overtime extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property string|null $description
 * @property bool $has_shift
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Shift> $shifts
 * @property-read int|null $shifts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Worker> $workers
 * @property-read int|null $workers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereHasShift($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereUpdatedAt($value)
 */
	class Position extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Worker> $workers
 * @property-read int|null $workers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Religion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Religion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Religion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Religion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Religion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Religion whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Religion whereUpdatedAt($value)
 */
	class Religion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $worker_id
 * @property numeric $basic_salary
 * @property numeric $allowance
 * @property numeric $deduction
 * @property numeric $total_salary
 * @property \Illuminate\Support\Carbon $payment_date
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Worker $worker
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereAllowance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereBasicSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary wherePaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereTotalSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereWorkerId($value)
 */
	class Salary extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property string $start_time
 * @property string $end_time
 * @property int $total_hours
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Position> $positions
 * @property-read int|null $positions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WorkerShiftSchedule> $schedules
 * @property-read int|null $schedules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Worker> $workers
 * @property-read int|null $workers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereTotalHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereUpdatedAt($value)
 */
	class Shift extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property array<array-key, mixed> $days_of_week e.g. ["mon","tue","wed"]
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftPattern newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftPattern newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftPattern query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftPattern whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftPattern whereDaysOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftPattern whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftPattern whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftPattern whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftPattern whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShiftPattern whereUpdatedAt($value)
 */
	class ShiftPattern extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $worker_id
 * @property string $email
 * @property string $username
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $last_login
 * @property string|null $remember_token
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \App\Models\Worker $worker
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereWorkerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $nip Nomor Induk Pegawai
 * @property string $name Nama lengkap
 * @property string|null $surname Nama belakang (opsional)
 * @property string|null $frontname Gelar depan (Dr., Prof., dll)
 * @property string|null $backname Gelar belakang (S.Kep, Sp.A, dll)
 * @property string $email
 * @property string $address Alamat lengkap
 * @property \Illuminate\Support\Carbon $birth_date Tanggal lahir
 * @property string $birth_place Tempat lahir
 * @property string $gender_id
 * @property string $religion_id
 * @property string $position_id
 * @property string $phone_number
 * @property string $status
 * @property \Illuminate\Support\Carbon $hire_date Tanggal bergabung
 * @property \Illuminate\Support\Carbon|null $resign_date Tanggal resign
 * @property string|null $photo_url URL foto profil
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Absent> $absents
 * @property-read int|null $absents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Shift> $activeShifts
 * @property-read int|null $active_shifts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Berkas> $berkas
 * @property-read int|null $berkas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BusinessTrip> $businessTrips
 * @property-read int|null $business_trips_count
 * @property-read \App\Models\Gender $gender
 * @property-read int|null $age
 * @property-read string $full_name
 * @property-read int|null $years_of_service
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LeaveRequest> $leaveRequests
 * @property-read int|null $leave_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Overtime> $overtimes
 * @property-read int|null $overtimes_count
 * @property-read \App\Models\Position $position
 * @property-read \App\Models\Religion $religion
 * @property-read \App\Models\Salary|null $salary
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WorkerShiftSchedule> $shiftSchedules
 * @property-read int|null $shift_schedules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Shift> $shifts
 * @property-read int|null $shifts_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker byPosition($positionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker inactive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker resigned()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereBackname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereBirthPlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereFrontname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereGenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereHireDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker wherePhotoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker wherePositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereReligionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereResignDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereSurname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Worker withoutTrashed()
 */
	class Worker extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $worker_id
 * @property string $shift_id
 * @property string|null $day_of_week
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $schedule_date
 * @property bool $is_override
 * @property string|null $replaced_worker_id
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Absent> $absents
 * @property-read int|null $absents_count
 * @property-read \App\Models\Worker|null $replacedWorker
 * @property-read \App\Models\Shift $shift
 * @property-read \App\Models\Worker $worker
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule default()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule forDate($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule forDay(string $dayOfWeek)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule forWorker($workerId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule override()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule whereDayOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule whereIsOverride($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule whereReplacedWorkerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule whereScheduleDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkerShiftSchedule whereWorkerId($value)
 */
	class WorkerShiftSchedule extends \Eloquent {}
}

