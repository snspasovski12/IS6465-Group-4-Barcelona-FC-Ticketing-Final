<?php
require_once __DIR__.'/crud.php';
$kind=$kind??'user'; $editing=$editing??false;
$admin=is_admin();
if ($kind==='user') {
    if ($editing) {
        $viewer=require_login(); $id=id_from_url('user_id')??(int)$viewer['UserID'];
        require_owner_or_admin($id);
        $record=db_one('SELECT * FROM users WHERE UserID=?',[$id]);
        if (!$record) { http_response_code(404); exit('User not found'); }
    } else { $record=[]; $id=null; }
    $back=$admin?'manage-users.php':'my-account.php';
} else {
    require_admin(); $id=$editing?id_from_url($kind.'_id'):null;
    if ($editing && !$id) { http_response_code(400); exit('Missing record ID'); }
    $table=$kind==='match'?'matches':'seats'; $key=$kind==='match'?'MatchID':'SeatID';
    $record=$editing?db_one("SELECT * FROM $table WHERE $key=?",[$id]):[];
    if ($editing && !$record) { http_response_code(404); exit('Record not found'); }
    $back=$kind==='match'?'manage-matches.php':'manage-seats.php';
}
$error=null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    try {
        if ($kind==='user') save_user($id,$admin);
        elseif ($kind==='match') save_match($id);
        else save_seat($id);
        flash(ucfirst($kind).' saved successfully.');
        redirect($kind==='user' && !$admin && !$editing?'login.php':$back);
    } catch (Throwable $ex) { $error=$ex instanceof PDOException?'Unable to save record. Check for duplicates or related records.':$ex->getMessage(); }
}
$title=($editing?'Update ':'Create ').ucfirst($kind); $pageTitle=$title;
require __DIR__.'/header.php';
function value_of(array $record,string $field,string $post,string $default=''): string { return (string)($_POST[$post]??$record[$field]??$default); }
function field(string $label,string $name,string $value,string $type='text',bool $required=true): void {
    echo '<div class="mb-3"><label class="form-label" for="'.e($name).'">'.e($label).'</label><input class="form-control" id="'.e($name).'" name="'.e($name).'" type="'.e($type).'" value="'.e($value).'" '.($required?'required':'').'></div>';
}
function choice(string $label,string $name,string $value,array $options): void {
    echo '<div class="mb-3"><label class="form-label" for="'.e($name).'">'.e($label).'</label><select class="form-select" id="'.e($name).'" name="'.e($name).'">';
    foreach($options as $option) echo '<option value="'.e($option).'" '.($option===$value?'selected':'').'>'.e($option).'</option>';
    echo '</select></div>';
}
?>
<div class="row justify-content-center"><div class="col-lg-7">
<h1 class="h3 mb-4"><?=e($title)?></h1>
<?php show_error($error); ?>
<form method="post"><?php csrf_field(); ?>
<?php if ($kind==='user'): ?>
<?php field('First Name','first',value_of($record,'FirstName','first')); ?>
<?php field('Last Name','last',value_of($record,'LastName','last')); ?>
<?php field('Username','username',value_of($record,'Username','username')); ?>
<?php field('Email','email',value_of($record,'Email','email'),'email'); ?>
<?php if (!$editing): ?>
<div class="mb-3"><label for="password" class="form-label">Password (minimum 10 characters)</label><input type="password" class="form-control" id="password" name="password" minlength="10" required autocomplete="new-password"></div>
<?php endif; ?>
<?php field('Contact Information','contact',value_of($record,'ContactInformation','contact'),'text',false); ?>
<?php if ($admin): ?>
<?php choice('Role (Admin Only)','role',value_of($record,'Role','role','Fan'),['Fan','Admin']); ?>
<?php if ($editing) choice('Account Status (Admin Only)','status',value_of($record,'AccountStatus','status','Active'),['Active','Inactive']); ?>
<?php endif; ?>
<?php elseif ($kind==='match'): ?>
<?php field('Opponent Team','opponent',value_of($record,'Opponent','opponent')); ?>
<?php field('Match Date','date',value_of($record,'Date','date'),'date'); ?>
<?php field('Match Time','time',value_of($record,'Time','time'),'time'); ?>
<?php choice('Match Type','type',value_of($record,'MatchType','type','League'),['League','Cup']); ?>
<?php choice('Match Status','status',value_of($record,'MatchStatus','status','Scheduled'),['Scheduled','Completed','Cancelled']); ?>
<?php else: ?>
<?php field('Section','section',value_of($record,'Section','section')); ?>
<?php field('Row','row',value_of($record,'Row','row'),'number'); ?>
<?php field('Seat Number','number',value_of($record,'Number','number'),'number'); ?>
<?php field('Price','price',value_of($record,'Price','price'),'number'); ?>
<?php choice('Status','status',value_of($record,'SeatStatus','status','Active'),['Active','Inactive']); ?>
<?php if ($editing && seat_has_history($id)): ?><p class="text-muted">This seat has ticket history. Its location and price cannot change, but its status may be updated.</p><?php endif; ?>
<?php endif; ?>
<button class="btn btn-primary" type="submit"><?=e($title)?></button>
<a class="btn btn-outline-secondary ms-2" href="<?=e($back)?>">Cancel</a>
</form>

<?php if ($kind === 'user' && !$editing && !$admin): ?>
    <p class="mt-3">
        Already have an account?
        <a href="login.php">Login Here</a>
    </p>
<?php endif; ?>

</div></div>
<?php require __DIR__.'/footer.php'; ?>
