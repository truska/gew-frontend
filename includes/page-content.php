<!-- START page-content.php -->

<section class="row body-area">
	<div class="col-lg-10 col-lg-offset-1 body-area-top">
	
	<?php
		$GLOBALS['cms_content_map'] = [];
		$GLOBALS['cms_content_debug'] = [];
		$selectcontent = "SELECT * FROM `content` WHERE `page` = '" . $rowpage['id'] . "' AND `showonweb` = 'Yes' ORDER BY `sort`  ";
		$querycontent = mysqli_query($conn,$selectcontent);
		$num_rows = mysqli_num_rows($querycontent);
		//echo "rows = " .$num_rows . "<br>";

			while ($rowcontent = mysqli_fetch_assoc($querycontent) )
			{
				//echo $rowcontent["title"] . " | " . $rowcontent["layout"] . "<br>" ;
			$selectcontentlayout = "SELECT `url`, `name` FROM `layout` WHERE `id` = " . $rowcontent['layout'] . " ";
			$querycontentlayout = mysqli_query($conn,$selectcontentlayout);
			$rowcontentlayout = mysqli_fetch_assoc($querycontentlayout) ;
			$layoutUrl = $rowcontentlayout["url"] ?? '';
			$layoutName = $rowcontentlayout["name"] ?? '';
				$contentMapItem = [
					'id' => $rowcontent["id"] ?? null,
					'name' => $rowcontent["name"] ?? $rowcontent["title"] ?? $rowcontent["heading"] ?? '',
					'layout' => $rowcontent["layout"] ?? '',
					'layout_url' => $layoutUrl,
					'layout_name' => $layoutName,
					'sort' => $rowcontent["sort"] ?? null,
				];
				$GLOBALS['cms_content_map'][] = $contentMapItem;
				$GLOBALS['cms_content_debug'][] = $contentMapItem;
				$contentItem = $rowcontent;
				$contentItem['layout_url'] = $layoutUrl;
				$contentItem['layout_name'] = $layoutName;
				$contentSourceFormId = isset($rowcontent['source_form_id']) && is_numeric((string) $rowcontent['source_form_id'])
					? (int) $rowcontent['source_form_id']
					: null;

//	echo $rowpage["title"] . " | " . $pageLayout . "<br>" ;
//	echo $rowcontent["title"] . " | " . $rowcontent["layout"] . " | " . $rowcontentlayout["url"] . "<br>" ;
					$layoutResolved = '';
					$layoutTrimmed = ltrim(trim((string) $layoutUrl), '/');
					$layoutBase = basename($layoutTrimmed);
					$layoutCandidates = [
						__DIR__ . '/' . $layoutTrimmed,
						__DIR__ . '/' . $layoutBase,
					];
					$layoutFallbackMap = [
						'col2general.php' => 'content-standard-by-cols.php',
						'multi-col-general.php' => 'content-standard-by-cols.php',
						'contact.php' => 'content-contact.php',
						'fullwidthtext.php' => 'content-standard.php',
						'itfixintro.php' => 'content-standard.php',
						'why-itfix-legacy.php' => 'content-standard.php',
					];
					$layoutIdFallbackMap = [
						12 => 'content-standard-by-cols.php',
					];
					if (isset($layoutFallbackMap[$layoutBase])) {
						$layoutCandidates[] = __DIR__ . '/' . $layoutFallbackMap[$layoutBase];
					}
					$layoutId = (int) ($rowcontent['layout'] ?? 0);
					if ($layoutId > 0 && isset($layoutIdFallbackMap[$layoutId])) {
						$layoutCandidates[] = __DIR__ . '/' . $layoutIdFallbackMap[$layoutId];
					}
					foreach ($layoutCandidates as $candidatePath) {
						if ($candidatePath !== '' && file_exists($candidatePath)) {
							$layoutResolved = $candidatePath;
							break;
						}
					}
					if ($layoutResolved !== '') {
						include $layoutResolved;
					} else {
						// Last-resort fallback so content still renders if layout mapping is stale.
						include __DIR__ . '/content-standard.php';
					}
			}
	
		?>
	
	</div>
</section>


<!-- END page-content.php -->
