<?php
require_once __DIR__.'/includes/ticketing.php';require_admin();$id=(int)($_GET['ticket_id']??$_POST['ticket_id']??0);$t=ticket_record($id);
if(!$t){http_response_code(404);exit('Ticket not found.');}$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 if($t['TicketStatus']!=='Pending' || ($_POST['status']??'')!=='Cancelled')$error='Only a Pending ticket can be changed to Cancelled here.';
 else {db_execute("UPDATE tickets SET TicketStatus='Cancelled' WHERE TicketID=? AND TicketStatus='Pending'",[$id]);redirect('manage-tickets.php');}
}
$pageTitle='Update Ticket';require __DIR__.'/includes/header.php';?>
<h1>Update Ticket <?=e(ticket_id_label($id))?></h1><p><?=e($t['Opponent'])?> — <?=e($t['Section'])?> / <?=(int)$t['Row']?> / <?=(int)$t['Number']?> — $<?=number_format((float)$t['TicketPrice'],2)?></p><p>Current status: <?=e($t['TicketStatus'])?></p>
<?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?>
<?php if($t['TicketStatus']==='Pending'):?><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="ticket_id" value="<?=$id?>"><label for="status">Status</label><select class="form-select mb-3" name="status" id="status"><option value="Pending">Pending</option><option value="Cancelled">Cancelled</option></select><button class="btn btn-primary">Update Ticket</button></form><?php else:?><p>This status cannot be edited here. Purchased tickets must be voided through transactions.</p><?php endif;?>
<a href="manage-tickets.php" class="btn btn-outline-secondary mt-2">Cancel</a><?php require __DIR__.'/includes/footer.php';?>
