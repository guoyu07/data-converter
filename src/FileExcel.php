<?php namespace  Digitaldream\DataConverter;

/**
 * Description of FileExcel
 *
 * @author Tuhin
 */
class FileExcel extends FileManager implements FileManagerInterface
{

    protected $reader;

    /**
     *
     * @var type 
     */
    protected $excel;

    /**
     * Active sheet title
     * @var string 
     */
    protected $title = 'main';

    /**
     *
     * @var type 
     */
    protected $writer;

    /**
     *
     * @var type 
     */
    protected $start_cell = 'A1';

    /**
     *
     * @var type 
     */
    protected $per_sheet = 3000;

    /**
     * Set Range of Cells to read e.g A1:D9
     * 
     * 
     */
    protected $range;

    public function __construct()
    {
        parent::__construct();
        //do some initialization stuff
        $this->excel = new \PHPExcel();
        $this->writer = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
    }

    /**
     * Set Background Colour and Make Top Row Fridge.
     * 
     * @param type $rgb
     */
    public function bgColor($rgb)
    {
        $this->excel->getActiveSheet()
            ->getStyle('A1:Z1')
            ->getFill()
            ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB($rgb);
        $this->excel->getActiveSheet()->freezePane('Z2');
    }

    /**
     * Read data from loaded excel file.
     * @param string $range If range is set then it also read data by range. e.g A1:D5 it will read data from A1 cell to D5 cell
     * @return \App\Libs\Report\FileExcel
     */
    public function read()
    {

        try {
            $retData = array($range = '');
            if (!empty($range)) {
                $this->range = $range;
            }
            $this->checkMimeType();
            $this->loadFile();
            if ($this->hasError()) {
                return $this;
            }
            $inputFileType = \PHPExcel_IOFactory::identify($this->file_path);
            $this->reader = \PHPExcel_IOFactory::createReader($inputFileType);
            $this->excel = $this->reader->load($this->file_path);
            $range = $this->getRange();
            if (!empty($range)) {
                $retData = $this->excel->getActiveSheet()->rangeToArray($range);
            } else {
                $retData = $this->excel->getActiveSheet()->toArray();
            }
            $this->data = $retData;
        } catch (\Exception $ex) {
            $this->setException($ex)->throwException($ex);
        }
        return $this;
    }

    /**
     * Append data to a existing excel file.
     * 
     * Get the Height Row +1  and then set start point A.234.
     * In this way data will be appended perfectly.
     * This will be useful when generating a long excel field which need couple of  minutes.
     * 
     * @param type $fileName
     */
    public function append($file_path = '')
    {
        $this->file_path = !empty($file_path) ? $file_path : $this->file_path;
        $this->append = TRUE;
        $this->excel = \PHPExcel_IOFactory::load($this->file_path);
        $this->writer = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');

        $row = $this->excel->getActiveSheet()->getHighestRow() + 1;
        $this->start_cell = 'A' . $row;
        return $this;
    }

    /**
     * Write data to excel file after done necessary modification
     * @return \App\Libs\Report\FileExcel
     */
    public function write()
    {
        $this->excel->getActiveSheet()->fromArray($this->data, null, $this->start_cell);
        $this->excel->getActiveSheet()->setTitle($this->title);
        $this->excel->setActiveSheetIndex(0);
//time() will output timestamp so that we can able to check when this file is created.


        $this->writer->save($this->file_path);
        return $this;
    }

    /**
     * Return Range. If range are set then it returns it or if  form and to property are set then it.
     *  For example, if from=A1 and to=D5 then it will make range as A1:D5
     * @return string
     */
    public function getRange()
    {
        if (!empty($this->range)) {
            return $this->range;
        } elseif (!empty($this->from)) {
            $this->range = $this->from;

            if (!empty($this->to)) {
                $this->range.=":" . $this->to;
            }
        }
        return $this->range;
    }
}
