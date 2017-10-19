<?php
namespace app\Helpers;

use mysqli;

class Mysql
{

    //连接数据库信息
    protected $host;    //连接地址

    protected $port;    //端口

    protected $username;    //连接用户名

    protected $password;    //连接密码

    protected $charset;     //数据库编码

    protected $collation;   //

    protected $isP;     //是否常连接

    protected $isC;     //是否连接

    protected $mysqli;


    //验证的信息
    protected $fillable;    //表的字段
    
    protected $hidden;      //隐藏的字段

    
    //SQL语句信息
    protected $table;   //表名

    protected $fields;  //字段

    protected $where;   //条件

    protected $sort;    //排序

    protected $limit;   //分页

    protected $group;   //汇总

    protected $insert;  //插入信息

    protected $update;  //更新数据

    protected $plus;    //加一字段

    protected $reduce;  //减一字段

    protected $sql; //SQL语句



    // var $mysqli;

    // var $serverName;    //数据库服务地址名字
    // var $user;          //连接账号
    // var $password;      //数据库连接密码
    // var $dbName;        //数据库名称

    // var $isP;   //是否持久连接
    // var $isC;   //是否已连接
    // var $charset = 'utf8';  //数据库连接字符集

    // var $isDebug = false;   //是否Debug模式

    // var $dbConnectId;       //数据库连接对象标识
    // var $result;            //执行查询返回的值
    
    // var $record;
    // var $rowset;
    // var $errno = 0;
    // var $error = "connection error";
    // var $checkDB;

    // var $sql = [];      //执行sql语句数组

    // //sql语句参数
    // var $parameter = [
    //     'table' => '',
    //     'fields' => [],
    //     'where' => [],
    //     'sort' => '',
    //     'limit' => '',
    //     'groupby' => '',

    //     'insert' => [],
    //     'update' => [],
    // ];


    public function __construct()
    {

        $this->host = config('database.mysql.host');    //连接地址

        $this->port = config('database.mysql.port');    //端口

        $this->database = config('database.mysql.database');    //数据库

        $this->username = config('database.mysql.username');    //连接用户名

        $this->password = config('database.mysql.password');    //连接密码

        $this->charset = config('database.mysql.charset');     //数据库编码

        $this->collation = config('database.mysql.collation');   //

        $this->isP = config('database.mysql.isP');;    //是否常连接

        $this->isC = false;


        //没有连接则连接
        if( empty($this->isC) )
        {
            $this->connection();
        }
        
    }

    //进行连接数据库
    public function connection()
    {
        //是否持久连接
        if( empty($this->isP) )
        {
            @$this->mysqli = new mysqli('p:'.$this->host.':'.$this->port, $this->username, $this->password, $this->database);
            //@$this->mysqli = new mysqli($this->host.':'.$this->port, $this->username, $this->password, $this->database);
        }
        else
        {
            @$this->mysqli = new mysqli($this->host.':'.$this->port, $this->username, $this->password, $this->database);
        }



        if(empty($this->mysqli))
        {
            die('连接失败');
        }

        //这只字符集
        $charset = $this->mysqli->get_charset();

        if($charset->charset != $this->charset)
        {
            $this->mysqli->set_charset($this->charset);
        }

        //检查连接
        if (mysqli_connect_errno()) {

            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();

        }

    }

    /***
     * 设置查询的表明
     * 
     * 格式：$table = 'user';
     */
    public function table($table)
    {
        //echo $table;
        $this->table = $table;

        return $this;
    }

    /***
     * 查询条件
     * 
     * 格式 [ ['id', '=' ,'26'], ['name', '=' ,'张三'] ]
     */
    public function where($where, $symbol=false, $value=false)
    {

        if( empty($symbol) && empty($value) )
        {
            //不是数据直接传值 字段为ID
            if(is_array($where) && count($where)>0 )
            {

                foreach($where as $key=>$value)
                {

                    if(!empty($key) && !empty($value))
                    {
                        $this->where[] = [$key, '=', $value];
                    }

                }

            }
            else
            {
                $this->where[] = ['id', '=', $where];
            }

        }
        else
        {
            $this->where[] = [$where, $symbol, $value];
        }
        
        return $this;

    }

    /***
     * 设置查询字段
     * 
     * 格式：$fields = ['id', 'name'];
     */
     public function fields($fields)
     {
         
         if(is_array($fields) && count($fields)>0)
         {
             foreach($fields as $key=>$value)
             {
                 if($this->checkField($value))
                 {
                     $this->fields[] = $value;
                 }
             }
         }
         else
         {
             if(!empty($fields) && $this->checkField($fields))
             {
                 $this->fields[] = $fields;
             }

         }

         return $this;
     }

    /***
     * 设置排序
     * 
     * 格式：$sort = 'id desc';
     */
    public function sort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /***
     * 设置查询数量
     * 
     * 格式：$limit = '0, 20';
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;        
    }

    /***
     * 查询汇总
     * 
     * 格式：$groupby = 'name';
     */
    public function groupby($groupby)
    {
        $this->groupby = $groupby;

        return $this;
        
    }

    /***
     * 进行查询
     *
     * get()
     */
    public function get($where=null)
    {

        //如果这里有值则是条件
        if($where)
        {
            $this->where($where);
        }

        //获取sql
        $sql = $this->makeSql();
    
        if($result = $this->runSql($sql))
        {
            $object = (object)[];

            $n = 0;
            
            while($row = $result->fetch_object())
            {
                
                $object->{$n} = $row;

                $n ++;
            }

            return $object;
        }

    }

    /***
     * 查询一条
     *
     * first()
     */
    public function first($where=null)
    {

        //如果这里有值则是条件
        if($where)
        {
            $this->where($where);
        }

        //获取sql
        $sql = $this->makeSql();
    
        if($result = $this->runSql($sql))
        {
            return $result->fetch_object();
        }

    }

    /***
     * ID定位查询
     *
     * find()
     */
    public function find($id)
    {

        //如果这里有值则是条件
        if($id)
        {
            $this->where($id);
        }

        //获取sql
        $sql = $this->makeSql();
    
        if($result = $this->runSql($sql))
        {
            return $result->fetch_object();
        }

    }

    /***
     * 插入数据
     * 
     * ['name'=>'张三', 'sex'=>'男']
     */
    public function insert($insert, $value=null)
    {
        if( is_array($insert) && count($insert)>0)
        {
            //过滤字段
            foreach($insert as $key=>$val)
            {
                if(checkField($key))
                {
                    unset($insert[$key]);
                }
 
            }

            $this->insert = $insert;
        }
        
        if(!is_array($insert) && $value)
        {
            $this->insert = [$insert=>$value];
        }


        //执行sql语句
        $sql = $this->makeSql('insert');
    
        if($result = $this->runSql($sql))
        {
            //返回受影响行数
            return $this->mysqli->affected_rows;
        }
        else
        {
            return false;
        }

    }

    /***
     * 更改数据
     *
     * update();
     */
    
    public function update($update, $value=null)
    {
        //数组格式传递更新数据
        if(is_array($update) && count($update)>0)
        {
            foreach($update as $key=>$value)
            {
                if(checkField($key))
                {
                    unset($insert[$key]);
                }
            }
        }
        

        //更新一个字段传值过来
        if(!is_array($update) && $value)
        {
            $this->update = [$update=>$value];
        }


        //获取sql
        $sql = $this->makeSql('update');

        //执行
        if($result = $this->runSql($sql))
        {
            //返回受影响行数
            return $this->mysqli->affected_rows;
        }
        else
        {
            return false;
        }

        
    }
    /***
     * 整数字段值增减
     *
     * plus()
     */
    public function plus($field)
    {

        $this->plus = $field;

        //获取sql
        $sql = $this->makeSql('update');

        //执行
        if($result = $this->runSql($sql))
        {
            //返回受影响行数
            return $this->mysqli->affected_rows;
        }
        else
        {
            return false;
        }
    }

    /***
     * 整数字段值减少
     *
     * reduce()
     */
    public function reduce($field)
    {
        $this->reduce = $field;

        //获取sql
        $sql = $this->makeSql('update');

        //执行
        if($result = $this->runSql($sql))
        {
            //返回受影响行数
            return $this->mysqli->affected_rows;
        }
        else
        {
            return false;
        }
    }

    /***
     * 执行SQL语句
     * 
     * runSql()
     */
    public function runSql($sql)
    {

        //SQL语句非法字符进行验证 和转义
        //------------------------------



        //------------------------------
        if($result = $this->mysqli->query($sql))
        {
            return $result;
        }
        else
        {
            echo $this->mysqli->error;
            
            echo '  [sql: '.$sql.']';

            die;
        }

    }

    /***
     * 组合sql语句
     * 
     * 组合分为四种 select insert update delete 四种组合
     */
    public function makeSql($type='select')
    {

        //拼接查询sql SELECT 列名称 FROM 表名称
        if( $type=='select' )
        {
            $sql = 'select ';
            
            //字段处理
            if( is_array($this->fields) && count($this->fields)>0 )
            {

                $sql .= implode(", ", $this->fields).' ';
            }
            else
            {
                $sql .= '* ';
            }

            //表名
            if( !empty($this->table) )
            {
                $sql .= 'from '.$this->table.' ';
            }
            else
            {
                return false;
            }

            //条件处理
            if( is_array($this->where) && count($this->where)>0 )
            {
                
                $sql .= 'where ';

                foreach($this->where as $key=>$value)
                {
                    if(!empty($value[0]) && !empty($value[1]) && !empty($value[2]))
                    {
                        if($key<=0)
                        {
                            $sql .= '`'.$value[0].'` '.$value[1].' "'.$value[2].'" ';
                        }
                        else
                        {
                            $sql .= 'and `'.$value[0].'` '.$value[1].' "'.$value[2].'" ';
                        }
                    }
                }
                
            }

            //排序
            if( !empty($this->sort) )
            {
                $sql .= 'order by '.$this->sort.' ';
            }

            //分页数量
            if( !empty($this->limit) )
            {
                $sql .= 'limit '.$this->limit.' ';
            }

            //汇总数量
            if( !empty($this->groupby) )
            {
                $sql .= 'groupby '.$this->groupby.' ';
            }

        }

        //插入语句 INSERT INTO table_name (列1, 列2,...) VALUES (值1, 值2,....)
        if( $type=='insert' )
        {
            $sql = 'insert into ';
            
            //表名
            if( !empty($this->table) )
            {
                $sql .= $this->table . ' ';
            }

            //插入的值和字段
            if( is_array($this->insert) && count($this->insert)>0 )
            {
                $sql_field = '';

                $sql_value = '';

                foreach($this->insert as $key=>$value)
                {
                    if( $key <= 0 )
                    {
                        $sql_field .= '`'.$key.'`';
                        $sql_value .= "'".$value."'";

                    }
                    else
                    {
                        $sql_field .= $key.',';
                        $sql_value .= $value.',';
                    }
                    
                }

                $sql .= '( '.$sql_field.' ) values( '.$sql_value.' ) ';

            }

            
        }

        //更新数据 UPDATE 表名称 SET 列名称 = 新值 WHERE 列名称 = 某值
        if( $type=='update' )
        {
            $sql = 'update ';

            //表名
            if( !empty($this->table) )
            {
                $sql .= $this->table . ' set ';
            }

            //更新数据
            if(is_array($this->update) && count($this->update)>0)
            {
                $sql .= 'set ';
                
                foreach($this->update as $key=>$value)
                {
                    if($key<=0)
                    {
                        $sql .= '`'.$key.'`=\''.$value.'\' ';
                    }
                    else
                    {
                        $sql .= '`'.$key.'`=\''.$value.'\', ';
                    }
                }
            }
            

            //更新加以数据
            if(!empty($this->plus))
            {
                $sql .= $this->plus.'='.$this->plus.'+1 ';
            }

            //更新减一数据
            if(!empty($this->reduce))
            {
                $sql .= $this->reduce.'='.$this->reduce.'-1 ';
            }

            //条件处理
            if( is_array($this->where) && count($this->where)>0 )
            {
                
                $sql .= 'where ';

                foreach($this->where as $key=>$value)
                {
                    if(!empty($value[0]) && !empty($value[1]) && !empty($value[2]))
                    {
                        if($key<=0)
                        {
                            $sql .= '`'.$value[0].'` '.$value[1].' "'.$value[2].'" ';
                        }
                        else
                        {
                            $sql .= 'and `'.$value[0].'` '.$value[1].' "'.$value[2].'" ';
                        }
                    }
                }
                
            }
        }

        //删除语句 DELETE FROM 表名称 WHERE 列名称 = 值
        if( $type=='delete' )
        {
            $sql = 'delete ';

            //表名
            if( !empty($this->table) )
            {
                $sql .= $this->table . ' ';
            }

            //删除的条件
            if( is_array($this->where) && count($this->where)>0 )
            {
                
                $sql .= 'where ';

                foreach($this->where as $key=>$value)
                {
                    if(!empty($value[0]) && !empty($value[1]) && !empty($value[2]))
                    {

                        if($key<=0)
                        {
                            $sql .= $value[0].$value[1].$value[2].' ';
                        }
                        else
                        {
                            $sql .= 'and '.$value[0].$value[1].$value[2].' ';
                        }
                        
                    }
                }
                
            }
        
        }

        return $sql;
        
    }

    /***
     * 表的字段检测
     *
     * checkField
     */
     public function checkField( $field )
     {
         if( empty($field) )
         {
             return false;
         }

         if( is_array($this->fillable) && count($this->fillable)>0 )
         {

             if(in_array($field, $this->fillable))
             {
                 return true;
             }
             else
             {
                 return false;
             }
         }
         else
         {
             return true;
         }
     }

    /***
     * 查询汇总
     * 
     * 格式：$groupby = 'name';
     */
    public function sql()
    {
        return $this->makeSql();
    }


    /***
     * 返回客户端
     * 
     * 格式 [ ['id', '=' ,'26'], ['name', '=' ,'张三'] ]
     */
    public function version() {
        return @mysql_get_server_info($this->dbConnectId);
    }


}