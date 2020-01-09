<?php
defined( '_JEXEC' ) or die;

/**
 * A helper class for displaying data in a simple table
 *
 * @package default
 * @author Ted Lowe
 */
class LibcsTableSimple
{
	function __construct()
	{
	}

	public static function ShowData(&$data, $caption = "", $width = "", $bCaptionOnly = false )
	{
		// PEAR dependency
		require_once 'HTML/Table.php';

		if ( ! empty( $caption ) )
			echo "<p style='font-weight: bold; font-family: arial; font-size: 12px;'>$caption</p>";

		if ( $bCaptionOnly )
			return;

		if ( count( $data ) == 0 )
		{
			echo "No data.";
			return;
		}

		$tableAttrs = array('border' => '1', 'cellpadding' => '2', 'cellspacing' => '1' );

		if ( ! empty( $width ) )
			$tableAttrs['width'] = $width;

		$table = new HTML_Table($tableAttrs);
		$table->setAutoGrow(true);
		$table->setAutoFill('&nbsp');

		$flds = array_keys($data[0]);
		$nflds = count($flds);
		$nrows = count($data );

		// header column labels
		for ( $c = 0 ; $c < $nflds ; $c++ )
			$table->setHeaderContents(0, $c, $flds[$c]); 

		// data
		for ( $r = 0 ; $r < $nrows ; $r++ )
			for ( $c = 0 ; $c < $nflds ; $c++ )
        		$table->setCellContents($r+1, $c, isset($data[$r][$flds[$c]]) ?  $data[$r][$flds[$c]] : ""); 

		// header row attribute
		$hrAttrs = array('style' => "text-align: left; font-weight: bold;" );
		$table->setRowAttributes(0, $hrAttrs);

		// column attributes
		$hrAttrs = array('style' => "font-family: arial; font-size: 11px;" );
		for ( $c = 0 ; $c < $nflds ; $c++ )
			$table->setColAttributes($c, $hrAttrs);

		echo $table->toHtml();
	}
}
