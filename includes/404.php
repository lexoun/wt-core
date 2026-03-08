<?php

$pagetitle = 'Stránka nenalezena';

include INCLUDES . "/head.php";

?>

<body class="page-body" data-url="http://neon.dev">

<div class="page-container"><!-- add class "sidebar-collapsed" to close sidebar by default, "chat-visible" to make chat appear always -->

	<?php include VIEW . '/default/menu.php';?>
	<div class="main-content">

		<div class="page-error-404" style="margin-top: 140px;">


	<div class="error-symbol">
		<i class="entypo-attention"></i>
	</div>

	<div class="error-text">
		<h2>404</h2>
		<p>Stránka nenalezena!</p>
	</div>

	<hr />

	<div class="error-text" style="display: none;">

		Search Pages:

		<br />
		<br />

		<div class="input-group minimal">
			<div class="input-group-addon">
				<i class="entypo-search"></i>
			</div>

			<input type="text" class="form-control" placeholder="Search anything..." />
		</div>

	</div>

</div>	</div>


	<!-- Bottom Scripts -->
	  <script src="<?= $home ?>/admin/assets/js/gsap/TweenMax.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/gsap/main-gsap.js"></script>
	<script src="<?= $home ?>/admin/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/bootstrap.js"></script>
	<script src="<?= $home ?>/admin/assets/js/joinable.js"></script>
	<script src="<?= $home ?>/admin/assets/js/resizeable.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-api.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-chat.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-custom.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-demo.js"></script>

</body>
</html>

<?