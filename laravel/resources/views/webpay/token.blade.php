<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">

</head>
<body>
	<form style="display: none" action="{{ $url }}" id="webpay" method="post">
	    @if ($type == 'NORMAL')<input type="hidden" name="token_ws" value="{{ $token }}">@endif
	    @if ($type == 'OC')<input type="hidden" name="TBK_TOKEN" value="{{ $token }}">@endif
	    <input type="submit" value="enviar">
	</form>

	<script type="text/javascript">document.getElementById("webpay").submit();</script>
</body>
</html>