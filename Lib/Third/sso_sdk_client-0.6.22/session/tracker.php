<?php
class Sso_Sdk_Session_Tracker {
    const STEP_0	=	0;
    const STEP_1	=	1;
    const STEP_2	=	2;
    const STEP_3	=	3;
    const STEP_4	=	4;
    const STEP_5	=	5;
    const STEP_6	=	6;
    const STEP_7	=	7;
    const STEP_8	=	8;
    const STEP_9	=	9;
    const STEP_10	=	10;
    const STEP_11	=	11;
    const STEP_12	=	12;
    const STEP_13	=	13;
    const STEP_14	=	14;
    const STEP_15	=	15;
    const V3_STEP_16=	16;
    const V3_STEP_17=	17;
    const V3_STEP_18=	18;
    const V3_STEP_19=	19;
    const V3_STEP_20=	20;
    const V3_STEP_21=	21;
    const V3_STEP_22=	22;
    const V3_STEP_23=	23;
    const V3_STEP_24=	24;
    const V3_STEP_25=	25;
    const V3_STEP_26=	26;
    const V3_STEP_27=	27;
    const V3_STEP_28=	28;
    const V3_STEP_29=	29;
    const V3_STEP_30=	30;
    const V3_STEP_31=	31;

    private static $_validate_path = 0;

    /**
     * @return int
     */
    public static function get_validate_path() {
        return self::$_validate_path;
    }

    /**
     * @param $step
     * @return int
     */
    public static function check_validate_path($step) {
        return self::$_validate_path & $step;
    }

    /**
     * @param $step
     */
    public static function update_validate_path($step) {
        self::$_validate_path |= (1<<$step);
    }

    /**
     * @return void
     */
    public static function reset_validate_path() {
        self::$_validate_path = 1;
    }

}
