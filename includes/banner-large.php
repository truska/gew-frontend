<!-- START banner-large -->


			<div class="banner cms-edit-target">
					<div class="desktop cms-edit-target">
					<?php
				$homeBannerImages = [];
				$homeBannerRecordId = 0;
				$homeBannerStrategy = 'content_welcome_layout_5';

				// 1) Primary: resolve the banner record from welcome-page content.id (layout 5).
				$selectHomeBannerContent = "SELECT c.`id` FROM `content` c
					INNER JOIN `pages` p ON p.`id` = c.`page`
					WHERE p.`slug` = 'welcome'
					AND c.`layout` = 5
					AND c.`showonweb` = 'Yes'
					AND c.`archived` = 0
					ORDER BY c.`sort` ASC, c.`id` DESC
					LIMIT 1";
				$queryHomeBannerContent = mysqli_query($conn, $selectHomeBannerContent);
				if ($queryHomeBannerContent instanceof mysqli_result) {
					$rowHomeBannerContent = mysqli_fetch_assoc($queryHomeBannerContent) ?: [];
					$homeBannerRecordId = (int) ($rowHomeBannerContent['id'] ?? 0);
				}

				// 2) Fallback: latest gallery record_id for form 8.
				if ($homeBannerRecordId <= 0) {
					$homeBannerStrategy = 'gallery_latest_record';
					$selectLatestGalleryRecord = "SELECT `record_id` FROM `gallery`
						WHERE `form_id` = 8
						AND `showonweb` = 'Yes'
						AND `archived` = 0
						AND `record_id` > 0
						ORDER BY `id` DESC
						LIMIT 1";
					$queryLatestGalleryRecord = mysqli_query($conn, $selectLatestGalleryRecord);
					if ($queryLatestGalleryRecord instanceof mysqli_result) {
						$rowLatestGalleryRecord = mysqli_fetch_assoc($queryLatestGalleryRecord) ?: [];
						$homeBannerRecordId = (int) ($rowLatestGalleryRecord['record_id'] ?? 0);
					}
				}

				// 3) Final fallback: old page/content context to avoid blanking.
				if ($homeBannerRecordId <= 0) {
					$homeBannerStrategy = 'legacy_page_context';
					$homeBannerRecordId = (int) (($rowcontent['id'] ?? 0));
					if ($homeBannerRecordId <= 0) {
						$homeBannerRecordId = (int) ($rowpage['id'] ?? 0);
					}
				}

				$selectHomeBanners = "SELECT `image`, `alttag`, `caption`, `title`, `subtitle`, `folder_name` FROM `gallery`
					WHERE `form_id` = 8
					AND `record_id` = " . $homeBannerRecordId . "
					AND `showonweb` = 'Yes'
					AND `archived` = 0
					ORDER BY `sort`, `id`";
				$queryHomeBanners = mysqli_query($conn, $selectHomeBanners);
				if ($queryHomeBanners instanceof mysqli_result) {
					while ($rowHomeBanner = mysqli_fetch_assoc($queryHomeBanners)) {
						$imageFile = trim((string) ($rowHomeBanner['image'] ?? ''));
						if ($imageFile === '') {
							continue;
						}
						$folderName = trim((string) ($rowHomeBanner['folder_name'] ?? ''), '/');
						if ($folderName === '') {
							$folderName = 'content';
						}
						$folderName = preg_replace('#^images/#', '', $folderName);
						$desktopImagePath = 'images/' . rtrim($folderName, '/') . '/lg/' . $imageFile;
						$altText = trim((string) ($rowHomeBanner['alttag'] ?? ''));
						if ($altText === '') {
							$altText = trim((string) ($rowHomeBanner['caption'] ?? ''));
						}
						if ($altText === '') {
							$altText = 'Home banner';
						}
						$homeBannerImages[] = [
							'src' => $baseURL . '/filestore/' . $desktopImagePath,
							'alt' => $altText,
							'title' => trim((string) ($rowHomeBanner['title'] ?? '')),
							'subtitle' => trim((string) ($rowHomeBanner['subtitle'] ?? '')),
						];
					}
				}
					$homeBannerCount = count($homeBannerImages);
					$bannerEditItem = [
						'id' => (int) $homeBannerRecordId,
						'table_name' => 'content',
					];
					?>
					<!-- home-banner-debug strategy=<?php echo $homeBannerStrategy; ?> record_id=<?php echo $homeBannerRecordId; ?> banner_rows=<?php echo $homeBannerCount; ?> -->
					<?php echo cms_render_frontend_edit_button($bannerEditItem); ?>

				<?php if ($homeBannerCount === 1): ?>
					<img src="<?php echo htmlspecialchars($homeBannerImages[0]['src'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($homeBannerImages[0]['alt'], ENT_QUOTES, 'UTF-8'); ?>">
					<?php if (!empty($homeBannerImages[0]['title']) || !empty($homeBannerImages[0]['subtitle'])): ?>
						<div class="carousel-caption container inner">
							<?php if (!empty($homeBannerImages[0]['title'])): ?>
								<h2 class="banner-title"><?php echo htmlspecialchars($homeBannerImages[0]['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
							<?php endif; ?>
							<?php if (!empty($homeBannerImages[0]['subtitle'])): ?>
								<p class="banner-subtitle"><?php echo htmlspecialchars($homeBannerImages[0]['subtitle'], ENT_QUOTES, 'UTF-8'); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php elseif ($homeBannerCount > 1): ?>
						<div id="home-banner-carousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
						<div class="carousel-inner" role="listbox">
							<?php foreach ($homeBannerImages as $index => $homeBanner): ?>
								<div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
									<img src="<?php echo htmlspecialchars($homeBanner['src'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($homeBanner['alt'], ENT_QUOTES, 'UTF-8'); ?>">
									<?php if (!empty($homeBanner['title']) || !empty($homeBanner['subtitle'])): ?>

										<div class="carousel-caption container inner">
											<?php if (!empty($homeBanner['title'])): ?>
												<h2 class="banner-title"><?php echo htmlspecialchars($homeBanner['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
											<?php endif; ?>
											<?php if (!empty($homeBanner['subtitle'])): ?>
												<p class="banner-subtitle"><?php echo htmlspecialchars($homeBanner['subtitle'], ENT_QUOTES, 'UTF-8'); ?></p>
											<?php endif; ?>
										</div>
										
									<?php endif; ?>

								</div>
							<?php endforeach; ?>
						</div>
						<button class="carousel-control-prev" type="button" data-bs-target="#home-banner-carousel" data-bs-slide="prev">
							<span class="carousel-control-prev-icon" aria-hidden="true"></span>
							<span class="visually-hidden">Previous</span>
						</button>
						<button class="carousel-control-next" type="button" data-bs-target="#home-banner-carousel" data-bs-slide="next">
							<span class="carousel-control-next-icon" aria-hidden="true"></span>
							<span class="visually-hidden">Next</span>
						</button>
					</div>
				<?php endif; ?>
			</div>
        
        
        
        
		<div class="mobile">
			<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                
  <?php
        // GET Sections for mobile home page 
	   $selectsection = "SELECT * FROM `menu` WHERE `submenu1` = '0' AND `showonhomemobile` = 'Yes' ORDER BY `menu`, `submenu` " ;
		$querysection = mysqli_query($conn,$selectsection);
		if (!($querysection instanceof mysqli_result)) {
			$querysection = null;
		}

        $counter = 1 ;        
		while ($querysection && ($rowsection = mysqli_fetch_assoc($querysection)) ) {

               
            echo "<div class='panel panel-default'>" ;
                echo "<div class='panel-heading' role='tab' id='heading". $counter . "'>" ;
                echo "<a role='button' class='collapsed' data-bs-toggle='collapse' data-toggle='collapse' data-bs-parent='#accordion' data-parent='#accordion' data-bs-target='#collapse". $counter . "' href='#collapse". $counter . "' aria-expanded='false' aria-controls='collapse". $counter . "'>
                         " . $rowsection["title"] . "<span class='caret'></span></a>" ;
                echo "</div>" ;
                echo "<div id='collapse". $counter . "' class='panel-collapse collapse' role='tabpanel' aria-labelledby='heading". $counter . "'>" ;
                    echo "<div class='panel-body'>" ;
                        echo "<ul>" ;

                        // GET Sections for mobile home page 
                       $selectproduct = "SELECT * FROM `menu` WHERE `menu` = " . $rowsection["menu"] . " AND `submenu` = " . $rowsection["submenu"] . " AND `submenu1` > '0' AND `showonweb` = 'Yes' ORDER BY `submenu1` " ;
                            $queryproduct = mysqli_query($conn,$selectproduct);
                            if (!($queryproduct instanceof mysqli_result)) {
                                $queryproduct = null;
                            }

                        while ($queryproduct && ($rowproduct = mysqli_fetch_assoc($queryproduct)) ) {
                            
                            // GET Page Slug 
                           $selectpageslug = "SELECT `slug` FROM `pages` WHERE `id` = " . $rowproduct["page"] . " " ;
                                $querypageslug = mysqli_query($conn,$selectpageslug);
                            	$rowpageslug = ($querypageslug instanceof mysqli_result)
                                    ? (mysqli_fetch_assoc($querypageslug) ?: [])
                                    : [];
                            
                            // GET Product Slug 
                           $selectproductslug = "SELECT `slug` FROM `products` WHERE `id` = " . $rowproduct["product"] . " " ;
                                $queryproductslug = mysqli_query($conn,$selectproductslug);
                            	$rowproductslug = ($queryproductslug instanceof mysqli_result)
                                    ? (mysqli_fetch_assoc($queryproductslug) ?: [])
                                    : [];
            
                            $pageSlug = $rowpageslug["slug"] ?? '';
                            $productSlug = $rowproductslug["slug"] ?? '';
                            echo "<li><a href='" . $baseURL . "/" . $pageSlug . "/" . $rowproduct["product"] . "/" . $productSlug . "'>" . $rowproduct["title"] . "</a></li>" ;
                        }
            
                        echo "</ul>" ;
                    echo "</div>" ;
                echo "</div>" ;
            echo "</div>" ;
            $counter ++ ;
        }
  ?>              
                
                
                
                
                
 <!-- ---------------------------------- -->               

                
 <!-- ---------------------------------- -->               
                
                
			</div>
		</div>
	</div> 


<!-- END banner-large.php -->
