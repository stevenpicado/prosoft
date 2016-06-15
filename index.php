<?php

if (isset($_POST['render_calendar'])) {

  # Validate number_of_days
  if (!is_numeric($_POST['number_of_days'])) {

    $error_number_of_days = 1;
  }

  # Validate country_code
  if (!strlen($_POST['country_code']) == 2) {

    $error_country_code = 1;
  }
  
  # Validate start_date, dates should come in 10 digits
  if (!strlen($_POST['start_date']) == 10) {

    $error_start_date = 1;
  } else {

    # If date is valid in length, split values.
    $month = explode('/', $_POST['start_date'])[0];
    $day = explode('/', $_POST['start_date'])[1];
    $year = explode('/', $_POST['start_date'])[2];

    # Get date together
    $start_date = $day . '-' . $month . '-' . $year;

    # What day of the week are we falling on?
    $weekday_value = date('N', strtotime($start_date));

    # Get ending date data
    $end_date_data = new DateTime($year . '-' . $month . '-' . $day);
    $end_date_data->add(new DateInterval('P' . $_POST['number_of_days'] . 'D'));
    $end_date = $end_date_data->format('Y-m-d');

    # Get number of months between start and end date
    $d1 = new DateTime($year . '-' . $month . '-' . $day);
    $d2 = new DateTime($end_date);

    $number_of_months = $d1->diff($d2)->m + ($d1->diff($d2)->y*12);

    for ($i = 0; $i <= $number_of_months ; $i++) { 

      # What is the first day of the selected month
      $first_day_in_month_number[$i] = date('N', strtotime(date('01-' . $month . '-' . $year) . " +" . $i . " month"));
      
      # How many days are there in selected months?
      $days_in_month[$i] = date('t', strtotime(date('01-' . $month . '-' . $year) . " +" . $i . " month"));

      # Get display year
      $display_year[$i] = date('Y', strtotime(date('01-' . $month . '-' . $year) . " +" . $i . " month"));

      # Get display month
      $display_month[$i] = date('m', strtotime(date('01-' . $month . '-' . $year) . " +" . $i . " month"));

      # Get number of empty slots to display after last day
      # The maximum amount of slots for a month is either 35 or 42, deduct 
      # the sum of $first_day_in_month_number and $days_in_month and to this 
      # number to get the required empty slots
      // Set max amount of slots
      ($first_day_in_month_number[$i] + $days_in_month[$i]) <= 35 ? $max_slot_amount[$i] = 35 : $max_slot_amount[$i] = 42;
      // Set last slot count
      $last_empty_slot_count[$i] = $max_slot_amount[$i] - ($first_day_in_month_number[$i] + $days_in_month[$i]);

      $holidays_json[$i] = file_get_contents('http://holidayapi.com/v1/holidays?country=' . $_POST['country_code'] . '&year=' . $display_year[$i] . '&month=' . $display_month[$i]);
      $obj[$i] = json_decode(utf8_decode($holidays_json[$i]), true);

      $status[$i] = $obj[$i]['status'];
      $holidays[$i] = $obj[$i]['holidays'];
      $holidays_i = 1;

      foreach ($holidays[$i] as $value[$i]) {

        $holiday_name[$holidays_i] = $value[$i]['name'];
        $holiday_date[$holidays_i] = $value[$i]['date'];
        $holidays_i++;
      }
    }

    # Errors
    // Month cannot be less than 1 and greater than 12
    $month < 1 || $month > 12 ? $error_start_date = 1 : '';
    // Day cannot be less than 1 and greater than 31
    $day < 1 || $day > 31 ? $error_start_date = 1 : '';
  }
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Programming UI Exercise</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
    <link rel="stylesheet" href="/css/select2.css" type="text/css" />

    <style type="text/css">

      tr > td:first-child, tr > td:last-child {
        background: yellow ! important;
      }
    </style>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body style="padding-top: 50px;">
    
    <div class="row">
      <div class="col-sm-12 col-sm-8 col-sm-offset-2">

        <h1 class="text-center">Please choose your options!</h1><hr>

        <form action="" method="post">
          
          <div class="col-sm-12 col-sm-3">
            <label for="start_date">Start date</label>
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
              <input name="start_date" type="text" class="form-control" id="start_date"<?= isset($_POST['start_date']) ? ' value="' . $_POST['start_date'] . '"' : ''; ?>>
            </div>
            <span class="help-block">mm/dd/yyyy</span>
          </div>

          <div class="col-sm-12 col-sm-3">
            <label for="number_of_days">Number of days</label>
            <div class="form-group">
              <input name="number_of_days" type="number" min="1" class="form-control text-center" id="number_of_days"<?= isset($_POST['number_of_days']) ? ' value="' . $_POST['number_of_days'] . '"' : ''; ?>>
            </div>
          </div>
          <div class="col-sm-12 col-sm-3">
            <label for="country_code">Country code</label>
            <div class="form-group form-group-select2" style="width: 100%;">
              <select name="country_code" id="country_code" style="width: 100%; margin-top: 4px;">
                <option value=""></option>
                <option value="AF"<?= $_POST['country_code'] == 'AF' ? ' selected' : ''; ?>>AF - Afghanistan</option>
                <option value="AX"<?= $_POST['country_code'] == 'AX' ? ' selected' : ''; ?>>AX - Åland Islands</option>
                <option value="AL"<?= $_POST['country_code'] == 'AL' ? ' selected' : ''; ?>>AL - Albania</option>
                <option value="DZ"<?= $_POST['country_code'] == 'DZ' ? ' selected' : ''; ?>>DZ - Algeria</option>
                <option value="AS"<?= $_POST['country_code'] == 'AS' ? ' selected' : ''; ?>>AS - American Samoa</option>
                <option value="AD"<?= $_POST['country_code'] == 'AD' ? ' selected' : ''; ?>>AD - Andorra</option>
                <option value="AO"<?= $_POST['country_code'] == 'AO' ? ' selected' : ''; ?>>AO - Angola</option>
                <option value="AI"<?= $_POST['country_code'] == 'AI' ? ' selected' : ''; ?>>AI - Anguilla</option>
                <option value="AQ"<?= $_POST['country_code'] == 'AQ' ? ' selected' : ''; ?>>AQ - Antarctica</option>
                <option value="AG"<?= $_POST['country_code'] == 'AG' ? ' selected' : ''; ?>>AG - Antigua and Barbuda</option>
                <option value="AR"<?= $_POST['country_code'] == 'AR' ? ' selected' : ''; ?>>AR - Argentina</option>
                <option value="AM"<?= $_POST['country_code'] == 'AM' ? ' selected' : ''; ?>>AM - Armenia</option>
                <option value="AW"<?= $_POST['country_code'] == 'AW' ? ' selected' : ''; ?>>AW - Aruba</option>
                <option value="AU"<?= $_POST['country_code'] == 'AU' ? ' selected' : ''; ?>>AU - Australia</option>
                <option value="AT"<?= $_POST['country_code'] == 'AT' ? ' selected' : ''; ?>>AT - Austria</option>
                <option value="AZ"<?= $_POST['country_code'] == 'AZ' ? ' selected' : ''; ?>>AZ - Azerbaijan</option>
                <option value="BS"<?= $_POST['country_code'] == 'BS' ? ' selected' : ''; ?>>BS - Bahamas</option>
                <option value="BH"<?= $_POST['country_code'] == 'BH' ? ' selected' : ''; ?>>BH - Bahrain</option>
                <option value="BD"<?= $_POST['country_code'] == 'BD' ? ' selected' : ''; ?>>BD - Bangladesh</option>
                <option value="BB"<?= $_POST['country_code'] == 'BB' ? ' selected' : ''; ?>>BB - Barbados</option>
                <option value="BY"<?= $_POST['country_code'] == 'BY' ? ' selected' : ''; ?>>BY - Belarus</option>
                <option value="BE"<?= $_POST['country_code'] == 'BE' ? ' selected' : ''; ?>>BE - Belgium</option>
                <option value="BZ"<?= $_POST['country_code'] == 'BZ' ? ' selected' : ''; ?>>BZ - Belize</option>
                <option value="BJ"<?= $_POST['country_code'] == 'BJ' ? ' selected' : ''; ?>>BJ - Benin</option>
                <option value="BM"<?= $_POST['country_code'] == 'BM' ? ' selected' : ''; ?>>BM - Bermuda</option>
                <option value="BT"<?= $_POST['country_code'] == 'BT' ? ' selected' : ''; ?>>BT - Bhutan</option>
                <option value="BO"<?= $_POST['country_code'] == 'BO' ? ' selected' : ''; ?>>BO - Bolivia, Plurinational State of</option>
                <option value="BQ"<?= $_POST['country_code'] == 'BQ' ? ' selected' : ''; ?>>BQ - Bonaire, Sint Eustatius and Saba</option>
                <option value="BA"<?= $_POST['country_code'] == 'BA' ? ' selected' : ''; ?>>BA - Bosnia and Herzegovina</option>
                <option value="BW"<?= $_POST['country_code'] == 'BW' ? ' selected' : ''; ?>>BW - Botswana</option>
                <option value="BV"<?= $_POST['country_code'] == 'BV' ? ' selected' : ''; ?>>BV - Bouvet Island</option>
                <option value="BR"<?= $_POST['country_code'] == 'BR' ? ' selected' : ''; ?>>BR - Brazil</option>
                <option value="IO"<?= $_POST['country_code'] == 'IO' ? ' selected' : ''; ?>>IO - British Indian Ocean Territory</option>
                <option value="BN"<?= $_POST['country_code'] == 'BN' ? ' selected' : ''; ?>>BN - Brunei Darussalam</option>
                <option value="BG"<?= $_POST['country_code'] == 'BG' ? ' selected' : ''; ?>>BG - Bulgaria</option>
                <option value="BF"<?= $_POST['country_code'] == 'BF' ? ' selected' : ''; ?>>BF - Burkina Faso</option>
                <option value="BI"<?= $_POST['country_code'] == 'BI' ? ' selected' : ''; ?>>BI - Burundi</option>
                <option value="KH"<?= $_POST['country_code'] == 'KH' ? ' selected' : ''; ?>>KH - Cambodia</option>
                <option value="CM"<?= $_POST['country_code'] == 'CM' ? ' selected' : ''; ?>>CM - Cameroon</option>
                <option value="CA"<?= $_POST['country_code'] == 'CA' ? ' selected' : ''; ?>>CA - Canada</option>
                <option value="CV"<?= $_POST['country_code'] == 'CV' ? ' selected' : ''; ?>>CV - Cape Verde</option>
                <option value="KY"<?= $_POST['country_code'] == 'KY' ? ' selected' : ''; ?>>KY - Cayman Islands</option>
                <option value="CF"<?= $_POST['country_code'] == 'CF' ? ' selected' : ''; ?>>CF - Central African Republic</option>
                <option value="TD"<?= $_POST['country_code'] == 'TD' ? ' selected' : ''; ?>>TD - Chad</option>
                <option value="CL"<?= $_POST['country_code'] == 'CL' ? ' selected' : ''; ?>>CL - Chile</option>
                <option value="CN"<?= $_POST['country_code'] == 'CN' ? ' selected' : ''; ?>>CN - China</option>
                <option value="CX"<?= $_POST['country_code'] == 'CX' ? ' selected' : ''; ?>>CX - Christmas Island</option>
                <option value="CC"<?= $_POST['country_code'] == 'CC' ? ' selected' : ''; ?>>CC - Cocos (Keeling) Islands</option>
                <option value="CO"<?= $_POST['country_code'] == 'CO' ? ' selected' : ''; ?>>CO - Colombia</option>
                <option value="KM"<?= $_POST['country_code'] == 'KM' ? ' selected' : ''; ?>>KM - Comoros</option>
                <option value="CG"<?= $_POST['country_code'] == 'CG' ? ' selected' : ''; ?>>CG - Congo</option>
                <option value="CD"<?= $_POST['country_code'] == 'CD' ? ' selected' : ''; ?>>CD - Congo, the Democratic Republic of the</option>
                <option value="CK"<?= $_POST['country_code'] == 'CK' ? ' selected' : ''; ?>>CK - Cook Islands</option>
                <option value="CR"<?= $_POST['country_code'] == 'CR' ? ' selected' : ''; ?>>CR - Costa Rica</option>
                <option value="CI"<?= $_POST['country_code'] == 'CI' ? ' selected' : ''; ?>>CI - Côte d'Ivoire</option>
                <option value="HR"<?= $_POST['country_code'] == 'HR' ? ' selected' : ''; ?>>HR - Croatia</option>
                <option value="CU"<?= $_POST['country_code'] == 'CU' ? ' selected' : ''; ?>>CU - Cuba</option>
                <option value="CW"<?= $_POST['country_code'] == 'CW' ? ' selected' : ''; ?>>CW - Curaçao</option>
                <option value="CY"<?= $_POST['country_code'] == 'CY' ? ' selected' : ''; ?>>CY - Cyprus</option>
                <option value="CZ"<?= $_POST['country_code'] == 'CZ' ? ' selected' : ''; ?>>CZ - Czech Republic</option>
                <option value="DK"<?= $_POST['country_code'] == 'DK' ? ' selected' : ''; ?>>DK - Denmark</option>
                <option value="DJ"<?= $_POST['country_code'] == 'DJ' ? ' selected' : ''; ?>>DJ - Djibouti</option>
                <option value="DM"<?= $_POST['country_code'] == 'DM' ? ' selected' : ''; ?>>DM - Dominica</option>
                <option value="DO"<?= $_POST['country_code'] == 'DO' ? ' selected' : ''; ?>>DO - Dominican Republic</option>
                <option value="EC"<?= $_POST['country_code'] == 'EC' ? ' selected' : ''; ?>>EC - Ecuador</option>
                <option value="EG"<?= $_POST['country_code'] == 'EG' ? ' selected' : ''; ?>>EG - Egypt</option>
                <option value="SV"<?= $_POST['country_code'] == 'SV' ? ' selected' : ''; ?>>SV - El Salvador</option>
                <option value="GQ"<?= $_POST['country_code'] == 'GQ' ? ' selected' : ''; ?>>GQ - Equatorial Guinea</option>
                <option value="ER"<?= $_POST['country_code'] == 'ER' ? ' selected' : ''; ?>>ER - Eritrea</option>
                <option value="EE"<?= $_POST['country_code'] == 'EE' ? ' selected' : ''; ?>>EE - Estonia</option>
                <option value="ET"<?= $_POST['country_code'] == 'ET' ? ' selected' : ''; ?>>ET - Ethiopia</option>
                <option value="FK"<?= $_POST['country_code'] == 'FK' ? ' selected' : ''; ?>>FK - Falkland Islands (Malvinas)</option>
                <option value="FO"<?= $_POST['country_code'] == 'FO' ? ' selected' : ''; ?>>FO - Faroe Islands</option>
                <option value="FJ"<?= $_POST['country_code'] == 'FJ' ? ' selected' : ''; ?>>FJ - Fiji</option>
                <option value="FI"<?= $_POST['country_code'] == 'FI' ? ' selected' : ''; ?>>FI - Finland</option>
                <option value="FR"<?= $_POST['country_code'] == 'FR' ? ' selected' : ''; ?>>FR - France</option>
                <option value="GF"<?= $_POST['country_code'] == 'GF' ? ' selected' : ''; ?>>GF - French Guiana</option>
                <option value="PF"<?= $_POST['country_code'] == 'PF' ? ' selected' : ''; ?>>PF - French Polynesia</option>
                <option value="TF"<?= $_POST['country_code'] == 'TF' ? ' selected' : ''; ?>>TF - French Southern Territories</option>
                <option value="GA"<?= $_POST['country_code'] == 'GA' ? ' selected' : ''; ?>>GA - Gabon</option>
                <option value="GM"<?= $_POST['country_code'] == 'GM' ? ' selected' : ''; ?>>GM - Gambia</option>
                <option value="GE"<?= $_POST['country_code'] == 'GE' ? ' selected' : ''; ?>>GE - Georgia</option>
                <option value="DE"<?= $_POST['country_code'] == 'DE' ? ' selected' : ''; ?>>DE - Germany</option>
                <option value="GH"<?= $_POST['country_code'] == 'GH' ? ' selected' : ''; ?>>GH - Ghana</option>
                <option value="GI"<?= $_POST['country_code'] == 'GI' ? ' selected' : ''; ?>>GI - Gibraltar</option>
                <option value="GR"<?= $_POST['country_code'] == 'GR' ? ' selected' : ''; ?>>GR - Greece</option>
                <option value="GL"<?= $_POST['country_code'] == 'GL' ? ' selected' : ''; ?>>GL - Greenland</option>
                <option value="GD"<?= $_POST['country_code'] == 'GD' ? ' selected' : ''; ?>>GD - Grenada</option>
                <option value="GP"<?= $_POST['country_code'] == 'GP' ? ' selected' : ''; ?>>GP - Guadeloupe</option>
                <option value="GU"<?= $_POST['country_code'] == 'GU' ? ' selected' : ''; ?>>GU - Guam</option>
                <option value="GT"<?= $_POST['country_code'] == 'GT' ? ' selected' : ''; ?>>GT - Guatemala</option>
                <option value="GG"<?= $_POST['country_code'] == 'GG' ? ' selected' : ''; ?>>GG - Guernsey</option>
                <option value="GN"<?= $_POST['country_code'] == 'GN' ? ' selected' : ''; ?>>GN - Guinea</option>
                <option value="GW"<?= $_POST['country_code'] == 'GW' ? ' selected' : ''; ?>>GW - Guinea-Bissau</option>
                <option value="GY"<?= $_POST['country_code'] == 'GY' ? ' selected' : ''; ?>>GY - Guyana</option>
                <option value="HT"<?= $_POST['country_code'] == 'HT' ? ' selected' : ''; ?>>HT - Haiti</option>
                <option value="HM"<?= $_POST['country_code'] == 'HM' ? ' selected' : ''; ?>>HM - Heard Island and McDonald Islands</option>
                <option value="VA"<?= $_POST['country_code'] == 'VA' ? ' selected' : ''; ?>>VA - Holy See (Vatican City State)</option>
                <option value="HN"<?= $_POST['country_code'] == 'HN' ? ' selected' : ''; ?>>HN - Honduras</option>
                <option value="HK"<?= $_POST['country_code'] == 'HK' ? ' selected' : ''; ?>>HK - Hong Kong</option>
                <option value="HU"<?= $_POST['country_code'] == 'HU' ? ' selected' : ''; ?>>HU - Hungary</option>
                <option value="IS"<?= $_POST['country_code'] == 'IS' ? ' selected' : ''; ?>>IS - Iceland</option>
                <option value="IN"<?= $_POST['country_code'] == 'IN' ? ' selected' : ''; ?>>IN - India</option>
                <option value="ID"<?= $_POST['country_code'] == 'ID' ? ' selected' : ''; ?>>ID - Indonesia</option>
                <option value="IR"<?= $_POST['country_code'] == 'IR' ? ' selected' : ''; ?>>IR - Iran, Islamic Republic of</option>
                <option value="IQ"<?= $_POST['country_code'] == 'IQ' ? ' selected' : ''; ?>>IQ - Iraq</option>
                <option value="IE"<?= $_POST['country_code'] == 'IE' ? ' selected' : ''; ?>>IE - Ireland</option>
                <option value="IM"<?= $_POST['country_code'] == 'IM' ? ' selected' : ''; ?>>IM - Isle of Man</option>
                <option value="IL"<?= $_POST['country_code'] == 'IL' ? ' selected' : ''; ?>>IL - Israel</option>
                <option value="IT"<?= $_POST['country_code'] == 'IT' ? ' selected' : ''; ?>>IT - Italy</option>
                <option value="JM"<?= $_POST['country_code'] == 'JM' ? ' selected' : ''; ?>>JM - Jamaica</option>
                <option value="JP"<?= $_POST['country_code'] == 'JP' ? ' selected' : ''; ?>>JP - Japan</option>
                <option value="JE"<?= $_POST['country_code'] == 'JE' ? ' selected' : ''; ?>>JE - Jersey</option>
                <option value="JO"<?= $_POST['country_code'] == 'JO' ? ' selected' : ''; ?>>JO - Jordan</option>
                <option value="KZ"<?= $_POST['country_code'] == 'KZ' ? ' selected' : ''; ?>>KZ - Kazakhstan</option>
                <option value="KE"<?= $_POST['country_code'] == 'KE' ? ' selected' : ''; ?>>KE - Kenya</option>
                <option value="KI"<?= $_POST['country_code'] == 'KI' ? ' selected' : ''; ?>>KI - Kiribati</option>
                <option value="KP"<?= $_POST['country_code'] == 'KP' ? ' selected' : ''; ?>>KP - Korea, Democratic People's Republic of</option>
                <option value="KR"<?= $_POST['country_code'] == 'KR' ? ' selected' : ''; ?>>KR - Korea, Republic of</option>
                <option value="KW"<?= $_POST['country_code'] == 'KW' ? ' selected' : ''; ?>>KW - Kuwait</option>
                <option value="KG"<?= $_POST['country_code'] == 'KG' ? ' selected' : ''; ?>>KG - Kyrgyzstan</option>
                <option value="LA"<?= $_POST['country_code'] == 'LA' ? ' selected' : ''; ?>>LA - Lao People's Democratic Republic</option>
                <option value="LV"<?= $_POST['country_code'] == 'LV' ? ' selected' : ''; ?>>LV - Latvia</option>
                <option value="LB"<?= $_POST['country_code'] == 'LB' ? ' selected' : ''; ?>>LB - Lebanon</option>
                <option value="LS"<?= $_POST['country_code'] == 'LS' ? ' selected' : ''; ?>>LS - Lesotho</option>
                <option value="LR"<?= $_POST['country_code'] == 'LR' ? ' selected' : ''; ?>>LR - Liberia</option>
                <option value="LY"<?= $_POST['country_code'] == 'LY' ? ' selected' : ''; ?>>LY - Libya</option>
                <option value="LI"<?= $_POST['country_code'] == 'LI' ? ' selected' : ''; ?>>LI - Liechtenstein</option>
                <option value="LT"<?= $_POST['country_code'] == 'LT' ? ' selected' : ''; ?>>LT - Lithuania</option>
                <option value="LU"<?= $_POST['country_code'] == 'LU' ? ' selected' : ''; ?>>LU - Luxembourg</option>
                <option value="MO"<?= $_POST['country_code'] == 'MO' ? ' selected' : ''; ?>>MO - Macao</option>
                <option value="MK"<?= $_POST['country_code'] == 'MK' ? ' selected' : ''; ?>>MK - Macedonia, the former Yugoslav Republic of</option>
                <option value="MG"<?= $_POST['country_code'] == 'MG' ? ' selected' : ''; ?>>MG - Madagascar</option>
                <option value="MW"<?= $_POST['country_code'] == 'MW' ? ' selected' : ''; ?>>MW - Malawi</option>
                <option value="MY"<?= $_POST['country_code'] == 'MY' ? ' selected' : ''; ?>>MY - Malaysia</option>
                <option value="MV"<?= $_POST['country_code'] == 'MV' ? ' selected' : ''; ?>>MV - Maldives</option>
                <option value="ML"<?= $_POST['country_code'] == 'ML' ? ' selected' : ''; ?>>ML - Mali</option>
                <option value="MT"<?= $_POST['country_code'] == 'MT' ? ' selected' : ''; ?>>MT - Malta</option>
                <option value="MH"<?= $_POST['country_code'] == 'MH' ? ' selected' : ''; ?>>MH - Marshall Islands</option>
                <option value="MQ"<?= $_POST['country_code'] == 'MQ' ? ' selected' : ''; ?>>MQ - Martinique</option>
                <option value="MR"<?= $_POST['country_code'] == 'MR' ? ' selected' : ''; ?>>MR - Mauritania</option>
                <option value="MU"<?= $_POST['country_code'] == 'MU' ? ' selected' : ''; ?>>MU - Mauritius</option>
                <option value="YT"<?= $_POST['country_code'] == 'YT' ? ' selected' : ''; ?>>YT - Mayotte</option>
                <option value="MX"<?= $_POST['country_code'] == 'MX' ? ' selected' : ''; ?>>MX - Mexico</option>
                <option value="FM"<?= $_POST['country_code'] == 'FM' ? ' selected' : ''; ?>>FM - Micronesia, Federated States of</option>
                <option value="MD"<?= $_POST['country_code'] == 'MD' ? ' selected' : ''; ?>>MD - Moldova, Republic of</option>
                <option value="MC"<?= $_POST['country_code'] == 'MC' ? ' selected' : ''; ?>>MC - Monaco</option>
                <option value="MN"<?= $_POST['country_code'] == 'MN' ? ' selected' : ''; ?>>MN - Mongolia</option>
                <option value="ME"<?= $_POST['country_code'] == 'ME' ? ' selected' : ''; ?>>ME - Montenegro</option>
                <option value="MS"<?= $_POST['country_code'] == 'MS' ? ' selected' : ''; ?>>MS - Montserrat</option>
                <option value="MA"<?= $_POST['country_code'] == 'MA' ? ' selected' : ''; ?>>MA - Morocco</option>
                <option value="MZ"<?= $_POST['country_code'] == 'MZ' ? ' selected' : ''; ?>>MZ - Mozambique</option>
                <option value="MM"<?= $_POST['country_code'] == 'MM' ? ' selected' : ''; ?>>MM - Myanmar</option>
                <option value="NA"<?= $_POST['country_code'] == 'NA' ? ' selected' : ''; ?>>NA - Namibia</option>
                <option value="NR"<?= $_POST['country_code'] == 'NR' ? ' selected' : ''; ?>>NR - Nauru</option>
                <option value="NP"<?= $_POST['country_code'] == 'NP' ? ' selected' : ''; ?>>NP - Nepal</option>
                <option value="NL"<?= $_POST['country_code'] == 'NL' ? ' selected' : ''; ?>>NL - Netherlands</option>
                <option value="NC"<?= $_POST['country_code'] == 'NC' ? ' selected' : ''; ?>>NC - New Caledonia</option>
                <option value="NZ"<?= $_POST['country_code'] == 'NZ' ? ' selected' : ''; ?>>NZ - New Zealand</option>
                <option value="NI"<?= $_POST['country_code'] == 'NI' ? ' selected' : ''; ?>>NI - Nicaragua</option>
                <option value="NE"<?= $_POST['country_code'] == 'NE' ? ' selected' : ''; ?>>NE - Niger</option>
                <option value="NG"<?= $_POST['country_code'] == 'NG' ? ' selected' : ''; ?>>NG - Nigeria</option>
                <option value="NU"<?= $_POST['country_code'] == 'NU' ? ' selected' : ''; ?>>NU - Niue</option>
                <option value="NF"<?= $_POST['country_code'] == 'NF' ? ' selected' : ''; ?>>NF - Norfolk Island</option>
                <option value="MP"<?= $_POST['country_code'] == 'MP' ? ' selected' : ''; ?>>MP - Northern Mariana Islands</option>
                <option value="NO"<?= $_POST['country_code'] == 'NO' ? ' selected' : ''; ?>>NO - Norway</option>
                <option value="OM"<?= $_POST['country_code'] == 'OM' ? ' selected' : ''; ?>>OM - Oman</option>
                <option value="PK"<?= $_POST['country_code'] == 'PK' ? ' selected' : ''; ?>>PK - Pakistan</option>
                <option value="PW"<?= $_POST['country_code'] == 'PW' ? ' selected' : ''; ?>>PW - Palau</option>
                <option value="PS"<?= $_POST['country_code'] == 'PS' ? ' selected' : ''; ?>>PS - Palestinian Territory, Occupied</option>
                <option value="PA"<?= $_POST['country_code'] == 'PA' ? ' selected' : ''; ?>>PA - Panama</option>
                <option value="PG"<?= $_POST['country_code'] == 'PG' ? ' selected' : ''; ?>>PG - Papua New Guinea</option>
                <option value="PY"<?= $_POST['country_code'] == 'PY' ? ' selected' : ''; ?>>PY - Paraguay</option>
                <option value="PE"<?= $_POST['country_code'] == 'PE' ? ' selected' : ''; ?>>PE - Peru</option>
                <option value="PH"<?= $_POST['country_code'] == 'PH' ? ' selected' : ''; ?>>PH - Philippines</option>
                <option value="PN"<?= $_POST['country_code'] == 'PN' ? ' selected' : ''; ?>>PN - Pitcairn</option>
                <option value="PL"<?= $_POST['country_code'] == 'PL' ? ' selected' : ''; ?>>PL - Poland</option>
                <option value="PT"<?= $_POST['country_code'] == 'PT' ? ' selected' : ''; ?>>PT - Portugal</option>
                <option value="PR"<?= $_POST['country_code'] == 'PR' ? ' selected' : ''; ?>>PR - Puerto Rico</option>
                <option value="QA"<?= $_POST['country_code'] == 'QA' ? ' selected' : ''; ?>>QA - Qatar</option>
                <option value="RE"<?= $_POST['country_code'] == 'RE' ? ' selected' : ''; ?>>RE - Réunion</option>
                <option value="RO"<?= $_POST['country_code'] == 'RO' ? ' selected' : ''; ?>>RO - Romania</option>
                <option value="RU"<?= $_POST['country_code'] == 'RU' ? ' selected' : ''; ?>>RU - Russian Federation</option>
                <option value="RW"<?= $_POST['country_code'] == 'RW' ? ' selected' : ''; ?>>RW - Rwanda</option>
                <option value="BL"<?= $_POST['country_code'] == 'BL' ? ' selected' : ''; ?>>BL - Saint Barthélemy</option>
                <option value="SH"<?= $_POST['country_code'] == 'SH' ? ' selected' : ''; ?>>SH - Saint Helena, Ascension and Tristan da Cunha</option>
                <option value="KN"<?= $_POST['country_code'] == 'KN' ? ' selected' : ''; ?>>KN - Saint Kitts and Nevis</option>
                <option value="LC"<?= $_POST['country_code'] == 'LC' ? ' selected' : ''; ?>>LC - Saint Lucia</option>
                <option value="MF"<?= $_POST['country_code'] == 'MF' ? ' selected' : ''; ?>>MF - Saint Martin (French part)</option>
                <option value="PM"<?= $_POST['country_code'] == 'PM' ? ' selected' : ''; ?>>PM - Saint Pierre and Miquelon</option>
                <option value="VC"<?= $_POST['country_code'] == 'VC' ? ' selected' : ''; ?>>VC - Saint Vincent and the Grenadines</option>
                <option value="WS"<?= $_POST['country_code'] == 'WS' ? ' selected' : ''; ?>>WS - Samoa</option>
                <option value="SM"<?= $_POST['country_code'] == 'SM' ? ' selected' : ''; ?>>SM - San Marino</option>
                <option value="ST"<?= $_POST['country_code'] == 'ST' ? ' selected' : ''; ?>>ST - Sao Tome and Principe</option>
                <option value="SA"<?= $_POST['country_code'] == 'SA' ? ' selected' : ''; ?>>SA - Saudi Arabia</option>
                <option value="SN"<?= $_POST['country_code'] == 'SN' ? ' selected' : ''; ?>>SN - Senegal</option>
                <option value="RS"<?= $_POST['country_code'] == 'RS' ? ' selected' : ''; ?>>RS - Serbia</option>
                <option value="SC"<?= $_POST['country_code'] == 'SC' ? ' selected' : ''; ?>>SC - Seychelles</option>
                <option value="SL"<?= $_POST['country_code'] == 'SL' ? ' selected' : ''; ?>>SL - Sierra Leone</option>
                <option value="SG"<?= $_POST['country_code'] == 'SG' ? ' selected' : ''; ?>>SG - Singapore</option>
                <option value="SX"<?= $_POST['country_code'] == 'SX' ? ' selected' : ''; ?>>SX - Sint Maarten (Dutch part)</option>
                <option value="SK"<?= $_POST['country_code'] == 'SK' ? ' selected' : ''; ?>>SK - Slovakia</option>
                <option value="SI"<?= $_POST['country_code'] == 'SI' ? ' selected' : ''; ?>>SI - Slovenia</option>
                <option value="SB"<?= $_POST['country_code'] == 'SB' ? ' selected' : ''; ?>>SB - Solomon Islands</option>
                <option value="SO"<?= $_POST['country_code'] == 'SO' ? ' selected' : ''; ?>>SO - Somalia</option>
                <option value="ZA"<?= $_POST['country_code'] == 'ZA' ? ' selected' : ''; ?>>ZA - South Africa</option>
                <option value="GS"<?= $_POST['country_code'] == 'GS' ? ' selected' : ''; ?>>GS - South Georgia and the South Sandwich Islands</option>
                <option value="SS"<?= $_POST['country_code'] == 'SS' ? ' selected' : ''; ?>>SS - South Sudan</option>
                <option value="ES"<?= $_POST['country_code'] == 'ES' ? ' selected' : ''; ?>>ES - Spain</option>
                <option value="LK"<?= $_POST['country_code'] == 'LK' ? ' selected' : ''; ?>>LK - Sri Lanka</option>
                <option value="SD"<?= $_POST['country_code'] == 'SD' ? ' selected' : ''; ?>>SD - Sudan</option>
                <option value="SR"<?= $_POST['country_code'] == 'SR' ? ' selected' : ''; ?>>SR - Suriname</option>
                <option value="SJ"<?= $_POST['country_code'] == 'SJ' ? ' selected' : ''; ?>>SJ - Svalbard and Jan Mayen</option>
                <option value="SZ"<?= $_POST['country_code'] == 'SZ' ? ' selected' : ''; ?>>SZ - Swaziland</option>
                <option value="SE"<?= $_POST['country_code'] == 'SE' ? ' selected' : ''; ?>>SE - Sweden</option>
                <option value="CH"<?= $_POST['country_code'] == 'CH' ? ' selected' : ''; ?>>CH - Switzerland</option>
                <option value="SY"<?= $_POST['country_code'] == 'SY' ? ' selected' : ''; ?>>SY - Syrian Arab Republic</option>
                <option value="TW"<?= $_POST['country_code'] == 'TW' ? ' selected' : ''; ?>>TW - Taiwan, Province of China</option>
                <option value="TJ"<?= $_POST['country_code'] == 'TJ' ? ' selected' : ''; ?>>TJ - Tajikistan</option>
                <option value="TZ"<?= $_POST['country_code'] == 'TZ' ? ' selected' : ''; ?>>TZ - Tanzania, United Republic of</option>
                <option value="TH"<?= $_POST['country_code'] == 'TH' ? ' selected' : ''; ?>>TH - Thailand</option>
                <option value="TL"<?= $_POST['country_code'] == 'TL' ? ' selected' : ''; ?>>TL - Timor-Leste</option>
                <option value="TG"<?= $_POST['country_code'] == 'TG' ? ' selected' : ''; ?>>TG - Togo</option>
                <option value="TK"<?= $_POST['country_code'] == 'TK' ? ' selected' : ''; ?>>TK - Tokelau</option>
                <option value="TO"<?= $_POST['country_code'] == 'TO' ? ' selected' : ''; ?>>TO - Tonga</option>
                <option value="TT"<?= $_POST['country_code'] == 'TT' ? ' selected' : ''; ?>>TT - Trinidad and Tobago</option>
                <option value="TN"<?= $_POST['country_code'] == 'TN' ? ' selected' : ''; ?>>TN - Tunisia</option>
                <option value="TR"<?= $_POST['country_code'] == 'TR' ? ' selected' : ''; ?>>TR - Turkey</option>
                <option value="TM"<?= $_POST['country_code'] == 'TM' ? ' selected' : ''; ?>>TM - Turkmenistan</option>
                <option value="TC"<?= $_POST['country_code'] == 'TC' ? ' selected' : ''; ?>>TC - Turks and Caicos Islands</option>
                <option value="TV"<?= $_POST['country_code'] == 'TV' ? ' selected' : ''; ?>>TV - Tuvalu</option>
                <option value="UG"<?= $_POST['country_code'] == 'UG' ? ' selected' : ''; ?>>UG - Uganda</option>
                <option value="UA"<?= $_POST['country_code'] == 'UA' ? ' selected' : ''; ?>>UA - Ukraine</option>
                <option value="AE"<?= $_POST['country_code'] == 'AE' ? ' selected' : ''; ?>>AE - United Arab Emirates</option>
                <option value="GB"<?= $_POST['country_code'] == 'GB' ? ' selected' : ''; ?>>GB - United Kingdom</option>
                <option value="US"<?= $_POST['country_code'] == 'US' ? ' selected' : ''; ?>>US - United States</option>
                <option value="UM"<?= $_POST['country_code'] == 'UM' ? ' selected' : ''; ?>>UM - United States Minor Outlying Islands</option>
                <option value="UY"<?= $_POST['country_code'] == 'UY' ? ' selected' : ''; ?>>UY - Uruguay</option>
                <option value="UZ"<?= $_POST['country_code'] == 'UZ' ? ' selected' : ''; ?>>UZ - Uzbekistan</option>
                <option value="VU"<?= $_POST['country_code'] == 'VU' ? ' selected' : ''; ?>>VU - Vanuatu</option>
                <option value="VE"<?= $_POST['country_code'] == 'VE' ? ' selected' : ''; ?>>VE - Venezuela, Bolivarian Republic of</option>
                <option value="VN"<?= $_POST['country_code'] == 'VN' ? ' selected' : ''; ?>>VN - Viet Nam</option>
                <option value="VG"<?= $_POST['country_code'] == 'VG' ? ' selected' : ''; ?>>VG - Virgin Islands, British</option>
                <option value="VI"<?= $_POST['country_code'] == 'VI' ? ' selected' : ''; ?>>VI - Virgin Islands, U.S.</option>
                <option value="WF"<?= $_POST['country_code'] == 'WF' ? ' selected' : ''; ?>>WF - Wallis and Futuna</option>
                <option value="EH"<?= $_POST['country_code'] == 'EH' ? ' selected' : ''; ?>>EH - Western Sahara</option>
                <option value="YE"<?= $_POST['country_code'] == 'YE' ? ' selected' : ''; ?>>YE - Yemen</option>
                <option value="ZM"<?= $_POST['country_code'] == 'ZM' ? ' selected' : ''; ?>>ZM - Zambia</option>
                <option value="ZW"<?= $_POST['country_code'] == 'ZW' ? ' selected' : ''; ?>>ZW - Zimbabwe</option>
              </select>
            </div>
          </div>
          <div class="col-sm-12 col-sm-3" style="padding-top: 25px;">
            <button type="submit" class="btn btn-primary">Go!</button>
          </div>

          <input type="hidden" name="render_calendar" value="1">
        </form>
      </div>

      <div class="col-sm-12 col-sm-6 col-sm-offset-3">

        <?php
        # Display error messages

        // Display selected parameters' data
        if (isset($_POST['render_calendar']) 
            && !isset($error_start_date)
            && !isset($error_number_of_days)
            && !isset($error_country_code)) { ?>

          <div class="alert alert-success" role="alert">
            <i class="fa fa-check"></i> 
            Displaying 
            <b><?= $_POST['number_of_days'] ?></b> 
            day<?= $_POST['number_of_days'] == 1 ? '' : 's'; ?> begining on 
            <b><?= date('l', strtotime($start_date)) . ', ' . date('F', strtotime($start_date)) . ' ' . date('j', strtotime($start_date)) . ' ' . date('Y', strtotime($start_date)) ?></b>!
          </div> <?php
          
        }

        // Error

        if ($error_start_date == 1) {
          
          # No start date warning ?>

          <div class="alert alert-danger" role="alert">
            <i class="fa fa-warning"></i> The start date field is required!
          </div> <?php
          
        }

        if ($error_number_of_days == 1) {
          
          # No number of days warning ?>

          <div class="alert alert-danger" role="alert">
            <i class="fa fa-warning"></i> The number of days field is required!
          </div> <?php
          
        }

        if ($error_country_code == 1) {
          
          # No country code warning ?>
          
          <div class="alert alert-danger" role="alert">
            <i class="fa fa-warning"></i> The country code field is required!
          </div> <?php
          
        }
        ?>
      </div>
    </div>

    <div class="row<?= isset($_POST['render_calendar']) && !isset($error_start_date) && !isset($error_number_of_days) && !isset($error_country_code) ? '' : ' hidden'; ?>">
      <div class="col-sm-12 col-sm-6 col-sm-offset-3 table-responsive">

        <?php
        # Display calendar(s)
        for ($i = 0; $i <= $number_of_months ; $i++) { ?>
          
          <h2 class="text-center"><?= date('F Y', strtotime($start_date . " +" . $i . " month")); ?></h2>

          <table class="table table-bordered" id="calendar<?= $i ?>">
            <tr>
              <th>Sunday</th>
              <th>Monday</th> 
              <th>Tuesday</th> 
              <th>Wednesday</th>
              <th>Thursday</th>
              <th>Friday</th>
              <th>Saturday</th>
            </tr>

            <tr>

              <?php
              # Empty slots ONLY if calendar does not begin in Sunday
              if ($first_day_in_month_number[$i] != 7) {
                
                for ($alt_i = 1; $alt_i <= $first_day_in_month_number[$i] ; $alt_i++) { 

                  echo '<td style="background: #444 ! important;"></td>';
                }
              }

              # Make calendar grid
              for ($alt_i = 1; $alt_i <= $days_in_month[$i] ; $alt_i++) { ?>

                <td style="background: #739900;"><?= $alt_i ?></td> <?php

                # Display post last day inactive slots string
                if ($alt_i == $days_in_month[$i]) {
                  
                  for ($x = 1; $x <= $last_empty_slot_count[$i] ; $x++) { 
                    
                    echo '<td style="background: #444 ! important;"></td>';
                  }
                }

                // Create new table row every 7 days
                if ($alt_i == (7 - $first_day_in_month_number[$i]) 
                  || $alt_i == (14 - $first_day_in_month_number[$i]) 
                  || $alt_i == (21 - $first_day_in_month_number[$i]) 
                  || $alt_i == (28 - $first_day_in_month_number[$i]) 
                  || $alt_i == (35 - $first_day_in_month_number[$i]) 
                  || $alt_i == (42 - $first_day_in_month_number[$i])) {
                  echo '<tr></tr>';
                }
              } ?>

            </tr>

          </table>

          <?php
        }
        ?>

      </div>
    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

    <script src="/js/jquery.maskedinput.min.js"></script>
    <script src="/js/bootstrap-datepicker.js"></script>
    <script src="/js/select2.min.js"></script>

    <script type="text/javascript">
      
      $(function () {
        
        // Start date mask
        $("#start_date").mask("99/99/9999");

        //nice select boxes
        $('#country_code').select2();
      });
    </script>
  </body>
</html>