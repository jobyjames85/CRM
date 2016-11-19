<?php
/*******************************************************************************
 *
 *  filename    : Include/ReportsConfig.php
 *  last change : 2003-03-14
 *  description : Configure report generation
 *
 *  http://www.churchcrm.io/
 *  Copyright 2004-2012 Chris Gebhardt, Michael Wilt
 *
 *  ChurchCRM is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 ******************************************************************************/

namespace ChurchCRM\Reports;

use FPDF;
// This class definition contains a bunch of configuration stuff and utitilities
// that are useful to all the reports generated by ChurchInfo

// Load the FPDF library

class ChurchInfoReport extends FPDF
{
  use ChurchCRM\dto\SystemConfig;
  //
  // Paper size for all PDF report documents
  // Sizes: A3, A4, A5, Letter, Legal, or a 2-element array for custom size
  // Sorry -- This should really be set in database, but it is needed before all the report settings
  // are read from the database.

  var $paperFormat = "Letter";

  function StripPhone($phone)
  {
    if (substr($phone, 0, 3) == SystemConfig::getValue("sHomeAreaCode"))
      $phone = substr($phone, 3, strlen($phone) - 3);
    if (substr($phone, 0, 5) == ("(" . SystemConfig::getValue("sHomeAreaCode") . ")"))
      $phone = substr($phone, 5, strlen($phone) - 5);
    if (substr($phone, 0, 1) == "-")
      $phone = substr($phone, 1, strlen($phone) - 1);
    if (strlen($phone) == 7) {
      // Fix the missing -
      $phone = substr($phone, 0, 3) . "-" . substr($phone, 3, 4);
    }
    return ($phone);
  }

  function PrintRightJustified($x, $y, $str)
  {
    $strconv = iconv("UTF-8", "ISO-8859-1", $str);
    $iLen = strlen($strconv);
    $nMoveBy = 10 - 2 * $iLen;
    $this->SetXY($x + $nMoveBy, $y);
    $this->Write($this->incrementY, $strconv);
  }

  function PrintRightJustifiedCell($x, $y, $wid, $str)
  {
    $strconv = iconv("UTF-8", "ISO-8859-1", $str);
    $iLen = strlen($strconv);
    $this->SetXY($x, $y);
    $this->Cell($wid, $this->incrementY, $strconv, 1, 0, 'R');
  }

  function PrintCenteredCell($x, $y, $wid, $str)
  {
    $strconv = iconv("UTF-8", "ISO-8859-1", $str);
    $iLen = strlen($strconv);
    $this->SetXY($x, $y);
    $this->Cell($wid, $this->incrementY, $strconv, 1, 0, 'C');
  }

  function WriteAt($x, $y, $str)
  {
    $strconv = iconv("UTF-8", "ISO-8859-1", $str);
    $this->SetXY($x, $y);
    $this->Write($this->incrementY, $strconv);
  }

  function WriteAtCell($x, $y, $wid, $str)
  {
    $strconv = iconv("UTF-8", "ISO-8859-1", $str);
    $this->SetXY($x, $y);
    $this->MultiCell($wid, 4, $strconv, 1);
  }

  function StartLetterPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, $letterhead = "")
  {
    global $sDefaultCountry;
    $this->AddPage();

    if ($letterhead == "graphic" && is_readable($this->bDirLetterHead)) {
      $this->Image($this->bDirLetterHead, 12, 15, 185);
      $curY = 20 + ($this->incrementY * 3) + 25;
      $this->WriteAt(170, $curY, date("m/d/Y"));
    } elseif ($letterhead == "none") {
      $curY = 20 + ($this->incrementY * 3) + 25;
      $this->WriteAt(170, $curY, date("m/d/Y"));
    } else {
      $dateX = 170;
      $dateY = 25;
      $this->WriteAt($dateX, $dateY, date("m/d/Y"));
      $curY = 20;
      $this->WriteAt($this->leftX, $curY, $this->sChurchName);
      $curY += $this->incrementY;
      $this->WriteAt($this->leftX, $curY, $this->sChurchAddress);
      $curY += $this->incrementY;
      $this->WriteAt($this->leftX, $curY, $this->sChurchCity . ", " . $this->sChurchState . "  " . $this->sChurchZip);
      $curY += $this->incrementY;
      $curY += $this->incrementY; // Skip another line before the phone/email
      $this->WriteAt($this->leftX, $curY, $this->sChurchPhone . "  " . $this->sChurchEmail);
      $curY += 25; // mm to move to the second window
    }
    $this->WriteAt($this->leftX, $curY, $this->MakeSalutation($fam_ID));
    $curY += $this->incrementY;
    if ($fam_Address1 != "") {
      $this->WriteAt($this->leftX, $curY, $fam_Address1);
      $curY += $this->incrementY;
    }
    if ($fam_Address2 != "") {
      $this->WriteAt($this->leftX, $curY, $fam_Address2);
      $curY += $this->incrementY;
    }
    $this->WriteAt($this->leftX, $curY, $fam_City . ", " . $fam_State . "  " . $fam_Zip);
    $curY += $this->incrementY;
    if ($fam_Country != "" && $fam_Country != $sDefaultCountry) {
      $this->WriteAt($this->leftX, $curY, $fam_Country);
      $curY += $this->incrementY;
    }
    $curY += 5.0; // mm to get away from the second window
    return ($curY);
  }

  function MakeSalutation($famID)
  {
    return (MakeSalutationUtility($famID));
  }

}

?>