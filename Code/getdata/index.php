<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
	$scanning = TRUE;
	require 'scan.php';
	
	$experimentList = array();
	$sessionList = array();
	$condList = array();
	foreach( $IDs as $id ) {
		$experimentList[ $id['Exp'] ] = TRUE;
		$sessionList[ $id['Sess'] ] = TRUE;
		$condList[ $id['Cond'] ] = $id['CndN'];
	}
	$experimentList = array_keys( $experimentList );
	$sessionList = array_keys( $sessionList );
	ksort($condList);
	
	$outputHeaders = array();
	foreach( $outputColumns as $header => $column ) {
		$category = substr( $header, 0, strpos( $header, '*' ) );
		if( $category === '' ) { $category = 'Misc.'; }
		$outputHeaders[ $category ][] = $header;
	}
	
	// quality checks of data
	foreach( $users as $user => $exps ) {
		$expFlags = array();
		if( count($exps) > 1 ) {
			$expFlags['Multiple Experiments'] = TRUE;
		}
		foreach( $exps as $conds ) {
			$conFlags = array();
			if( count($conds) > 1 ) {
				$conFlags['Multiple Conditions'] = TRUE;
			}
			foreach( $conds as $sessS ) {
				$i = 1;
				while( isset( $sessS[$i] ) ) { ++$i; }
				$sesFlags = array();
				if( count($sessS) < ($i-1) ) {
					$sesFlags['Missing Sessions'] = TRUE;
				}
				foreach( $sessS as $ids ) {
					$idsFlags = array();
					if( count($ids) > 1 ) {
						$idsFlags['Multiple IDs'] = TRUE;
					}
					foreach( $ids as $id => $fileName ) {
						$IDs[$id]['Error Flags'] = $expFlags + $conFlags + $sesFlags + $idsFlags;
					}
				}
			}
		}
	}
	$categories = array_keys( $extraFileMeta );
	foreach( $IDs as $i => $id ) {
		foreach( $categories as $category ) {
			if( !isset( $extraFileMeta[$category]['files'] ) ) { continue; }		// if this file doesn't exist, no need to flag IDs that don't have this data
			if( !isset( $id[ $category ] ) ) {
				$IDs[$i]['Error Flags']['Missing '.$category] = TRUE;
			} elseif( getArrayDims( $id[$category] ) > 1 ) {
				$IDs[$i]['Error Flags']['Overlapping '.$category] = TRUE;
			}
		}
	}
	$userFlags = array();
	$flagTypes = array();
	foreach( $IDs as $i => $id ) {
		foreach( $id['Error Flags'] as $flag => $true ) {
			$userFlags[ $id['Name'] ][$flag] = TRUE;
			$flagTypes[$flag] = TRUE;
		}
	}
	$images = array(
		 'Multiple Experiments' 		=> 'E'
		,'Multiple Conditions' 			=> 'C'
		,'Missing Sessions' 			=> 'S'
		,'Multiple IDs' 				=> 'I'
		,'Missing Demographics' 		=> 'D2'
		,'Missing Final_Questions' 		=> 'F2'
		,'Missing Instructions' 		=> 'I2'
		,'Missing Status_Begin' 		=> 'B2'
		,'Missing Status_End' 			=> 'E2'
		,'Overlapping Demographics' 	=> 'D1'
		,'Overlapping Final_Questions' 	=> 'F1'
		,'Overlapping Instructions' 	=> 'I1'
		,'Overlapping Status_Begin' 	=> 'B1'
		,'Overlapping Status_End' 		=> 'E1'
	);
	foreach( $images as $err => $code ) {
		if( !isset( $flagTypes[$err] ) ) {
			unset( $images[$err] );
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>GetData Menu</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<link href="getdataStyling.css" rel="stylesheet" type="text/css" />
	<script src="http://code.jquery.com/jquery-1.8.0.min.js" type="text/javascript"> </script>
</head>
<body id="GetDataMenu">
	<div id="Title">GetData</div>
	
	<form method="post" target="_blank" action="generate.php" id="form" >
	
		<div class="BlockStacker" id="FilterBlock">
			<div class="MenuBlock">
				<div class="Head">Main Menu</div>
				
				<div class="Column Half">
					<div class="InputContainer">						
						<?php
							createBlock( 'Main Data' , array('Demographics', 'Experiment', 'Final Questions') );
							createBlock( 'Extra Data', array('Status Begin', 'Status End', 'Instructions'), FALSE );
							createBlock( 'File Type', array( 'txt', 'csv', 'browser' ), TRUE, TRUE );
							// createBlock( 'Completion', array( 'Any', 'Session' ), TRUE, TRUE );		// this can be put back after multi-session is upgraded
						?>
					</div>
				</div>
				
				<div class="Column Half">
					<div class="InputContainer">
						<?php
							createBlock( 'Experiments', $experimentList, TRUE, FALSE, TRUE );
							createBlock( 'Conditions', array_keys($condList), TRUE, FALSE, TRUE, array_values($condList), '.' );
							createBlock( 'Session', $sessionList, TRUE, FALSE, TRUE );
						?>
						
					</div>
				</div>
				
				<input type="submit" value="Get Data" />
				
			</div>
			
			<div class="MenuBlock">
				<div class="Head">Row Filters</div>
				
				<div class="Column Half">
					<div class="InputContainer">
						<?php
							createBlock( 'Trial Types', $trialTypes, TRUE, FALSE, TRUE );
						?>
					</div>
				</div>
				
				<div class="Column Half">
					<div class="InputContainer">
						<?php
							createBlock( 'Timing', array( 'Numeric', 'Not Numeric' ), TRUE, FALSE, TRUE );
						?>
						<div class="Head">Trials</div>
						<input type="text" name="Trials" />
					</div>
				</div>
				
			</div>
			
			<div class="MenuBlock">
				<div class="Head">Column Filters</div>
				
				<?php
					foreach( $outputHeaders as $category => $columns ) {
						echo '<div class="Column Half">
								<div class="InputContainer">';
						createBlock( $category, $columns, TRUE, FALSE, TRUE, array(), '', 'Columns' );
						echo '	</div>
							</div>';
					}
				?>
				
			</div>
			
			<div class="MenuBlock">
				<?php
					foreach( $extraFileMeta as $category => $fileMeta ) {
						if( $category === 'Final_Questions' ) { continue; }
						echo '<div class="Column Half">
								<div class="InputContainer">';
						createBlock( $category, array_keys($fileMeta['Columns']), TRUE, FALSE, TRUE, array(), '', $category.'_Columns' );
						echo '	</div>
							</div>';
					}
					echo '<div class="Column">
							<div class="InputContainer">
								<div class="BlockOptions">
									<div class="Head">Final_Questions</div>';
					foreach( array_keys($extraFileMeta['Final_Questions']['Columns']) as $column ) {
						$display = '<div><div class="FinalQuestion">'.htmlspecialchars($column).'</div></div>';
						echo '		<label class="ChoiceLabel"> <div><input type="checkbox" name="Final_Questions_Columns[]" value="'.$column.'" 	checked	class="FQcheck" /></div> '.$display.'	</label>';
					}
					echo '</div></div></div>';
				?>
			</div>
			
		</div>
		
		<div class="BlockStacker" id="OutputBlock">
			
			<div class="MenuBlock">
				<div class="Head">Output Files</div>
				<div class="Column">
					<div class="InputContainer OutputTable">
						<div class="BlockOptions">
							<div class="Head">Good IDs</div>
							<?php
								foreach( $users as $user => $exps ) {
									if( isset( $userFlags[ $user ] ) ) { continue; }
									foreach( $exps as $exp => $conds ) {
										foreach( $conds as $cond => $sessS ) {
											foreach( $sessS as $sess => $ids ) {
												foreach( $ids as $id => $fileName ) {
													echo '<label class="ChoiceLabel">
															<div class="OutputTableCell"><input type="checkbox" name="IDs[]"	value="'.$id.'" 	checked	/></div>
															<div class="OutputTableCell Username">'.$user.'</div>
															<div class="OutputTableCell">'.date( 'M d, h:ia', strtotime( $IDs[$id]['Date'] ) ).'</div>
															<div class="OutputTableCell">C-'.$IDs[$id]['Cond'].'</div>
															<div class="OutputTableCell">S-'.$IDs[$id]['Sess'].'</div>
															<div class="OutputTableCell">'.$id.'</div>';
													foreach( $flagTypes as $true ) {
														echo '<div class="OutputTableCell"></div>';
													}
													echo '</label>';
												}
											}
										}
									}
								}
							?>
						</div>
						
						<?php if( count($userFlags)>0 ) { ?>
						<div class="BlockOptions">
							<div class="Head">Flagged IDs</div>
							<?php
								foreach( $users as $user => $exps ) {
									if( !isset( $userFlags[ $user ] ) ) { continue; }
									foreach( $exps as $exp => $conds ) {
										foreach( $conds as $cond => $sessS ) {
											foreach( $sessS as $sess => $ids ) {
												foreach( $ids as $id => $fileName ) {
													$i=1;
													$flags = SortArrayLikeArray( $IDs[$id]['Error Flags'], $images );
													echo '<label class="ChoiceLabel">
															<div class="OutputTableCell"><input type="checkbox" name="IDs[]"	value="'.$id.'" 	/></div>
															<div class="OutputTableCell Username">'.$user.'</div>
															<div class="OutputTableCell">'.date( 'M d, h:ia', strtotime( $IDs[$id]['Date'] ) ).'</div>
															<div class="OutputTableCell">C-'.$IDs[$id]['Cond'].'</div>
															<div class="OutputTableCell">S-'.$IDs[$id]['Sess'].'</div>
															<div class="OutputTableCell">'.$id.'</div>';
													foreach( $flags as $name => $val ) {
														if( $val === '' ) {
															echo '<div class="OutputTableCell"></div>';
														} else {
															echo '<div class="OutputTableCell WarningFlag" data-tooltip="'.$images[$name].'"><img src="images/'.$images[$name].'.png"></div>';
														}
														++$i;
													}
													echo '</label>';
												}
											}
										}
									}
								}
							?>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		
		</div>
		
	</form>
	
	<script>
		$(".BlockOptions .Head").each(
			function() {
				if( $(this).closest(".BlockOptions").find(".ChoiceLabel input[type='radio']").length == 0 ) {
					this.outerHTML = '<label class="HeadLabel"> <input type="checkbox" /> <span class="HeadNoLine">'+this.innerHTML+'</span></label>';
				}
			}
		);
		$(".HeadLabel > input[type='checkbox']").each( function() {
			if( $(this).closest(".BlockOptions").find(".ChoiceLabel input:checked").length == $(this).closest(".BlockOptions").find(".ChoiceLabel input").length ) {
				$(this).prop("checked", true);
			}
		});
		$(".HeadLabel > input[type='checkbox']").change( function(){
			$(this).closest(".BlockOptions").find(".ChoiceLabel input").prop("checked", $(this).prop("checked") );
		});
		$(".ChoiceLabel input[type='checkbox']").change( function(){
			var checked = $(this).closest(".BlockOptions").find(".ChoiceLabel input:checked").length;
			var count = $(this).closest(".BlockOptions").find(".ChoiceLabel input").length;
			if( checked == 0 ) {
				 $(this).closest(".BlockOptions").find(".HeadLabel input")[0].indeterminate = false;
				 $(this).closest(".BlockOptions").find(".HeadLabel input").prop("checked", false);
			} else if ( checked == count ) {
				 $(this).closest(".BlockOptions").find(".HeadLabel input")[0].indeterminate = false;
				 $(this).closest(".BlockOptions").find(".HeadLabel input").prop("checked", true);
			} else {
				 $(this).closest(".BlockOptions").find(".HeadLabel input")[0].indeterminate = true;
			}
		});
		var ErrorMessages = new Array();
		<?php
			foreach( $images as $err => $code ) {
				echo 'ErrorMessages["'.$code.'"] = "'.$err.'";';
			}
		?>
		var numberOfColumns = $(".OutputTable").find("label:first").children().length;
		var maxWidth;
		for( var i=1; i<= numberOfColumns; ++i ) {
			maxWidth = Math.max.apply(Math, $(".OutputTable").find("label :nth-child("+i+")").map(function(){ return $(this).width(); }).get());
			$(".OutputTable").find("label :nth-child("+i+")").width( maxWidth );
		}
		$(".WarningFlag").hover(
			function() {
				$( this ).append( $( "<span>"+ErrorMessages[ $( this ).data( "tooltip" ) ]+"</span>" ) );
			},
			function() {
				$( this ).find( "span:last" ).remove();
			}
		);
		function setFormTarget( fileType ) {
			var form = document.getElementById("form");
			if( fileType === "browser" ) {
				form.target = "_blank";
			} else {
				form.target = "_self";
			}
		}
		$('input[name="File_Type"]').change( function(){ setFormTarget(this.value); });
		jQuery.fn.forceNumeric = function () {
			// forceNumeric() plug-in implementation
			// I got forceNumeric from http://weblog.west-wind.com/posts/2011/Apr/22/Restricting-Input-in-HTML-Textboxes-to-Numeric-Values
			return this.each(function () {
				$(this).keydown(function (e) {
					var key = e.which || e.keyCode;
					
					if (!e.shiftKey && !e.altKey && !e.ctrlKey &&
					 // numbers   
						key >= 48 && key <= 57 ||
					 // Numeric keypad
						key >= 96 && key <= 105 ||
					 // comma, period and minus, . on keypad
					 //	key == 190 || key == 188 || key == 109 || key == 110 ||
						key == 189 || key == 188 || key == 109 ||
					 // Backspace and Tab and Enter
						key == 8 || key == 9 || key == 13 ||
					 // Home and End
						key == 35 || key == 36 ||
					 // left and right arrows
						key == 37 || key == 39 ||
					 // Del and Ins
						key == 46 || key == 45)
						return true;
					
					return false;
				});
			});
		}
		$("input[name='Trials']").forceNumeric();
		$("input[name='Trials']").keypress( function(e) {
			var key = e.which || e.keyCode;
			if( key == 13 ) {
			alert(key);
				return false;
			}
		});
	</script>
	
</body>
</html>