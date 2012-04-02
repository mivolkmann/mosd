 function deleteLog(id, type)
{
   var req = new request('/logs');
   req.setParam('aktion', type == 1 ? 'delete_fehler' : 'delete_event');
   req.setParam('logid', id);
   req.setFunction(function()
   {
      location.reload();
   });
   req.send();
}