#!/bin/bash
#
# Magento Enterprise Edition
#
# NOTICE OF LICENSE
#
# This source file is subject to the Magento Enterprise Edition End User License Agreement
# that is bundled with this package in the file LICENSE_EE.txt.
# It is also available through the world-wide-web at this URL:
# http://www.magento.com/license/enterprise-edition
# If you did not receive a copy of the license and are unable to
# obtain it through the world-wide-web, please send an email
# to license@magento.com so we can send you a copy immediately.
#
# DISCLAIMER
#
# Do not edit or add to this file if you wish to upgrade Magento to newer
# versions in the future. If you wish to customize Magento for your
# needs please refer to http://www.magento.com for more information.
#
# @category    Mage
# @package     Mage_Shell
# @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
# @license http://www.magento.com/license/enterprise-edition
#

if [[ -e ../../app/etc/local.xml ]]
then

  if [[ ! -d ../../var/support ]]
  then
    mkdir ../../var/support ||
    if [[ $? != 0 ]]
    then
      echo "Can't create output directory, aborting!"
      exit 1
    fi
    chmod 777 ../../var/support
  fi

  rep=../../var/support/`hostname --fqdn`-`date +%Y.%m.%d-%H_%M_%S`.log

  mbase=`echo $PWD | sed -e 's/\/shell\/support$//'`
  echo "Magento root: "${mbase} > ${rep}

else

  rep=`hostname --fqdn`-`date +%Y.%m.%d-%H_%M_%S`.log

  echo 'No Magento instance found!' > ${rep}

fi

#echo "rep = \"${rep}\""

echo '-------' >> ${rep}


echo 'Hostname: '`hostname --fqdn` >> ${rep} 2>&1
echo '-------' >> ${rep}

uname -a >> ${rep} 2>&1
echo '-------' >> ${rep}

cat /etc/issue >> ${rep} 2>&1
echo '-------' >> ${rep}

if [[ -f /etc/redhat-release ]]
then
  cat /etc/redhat-release >> ${rep} 2>&1
  echo '-------' >> ${rep}
fi

echo 'Runlevel: '`/sbin/runlevel | cut -c 3-` >> ${rep} 2>&1
echo '-------' >> ${rep}

echo 'CPUs: '`cat /proc/cpuinfo | grep -c '^processor'` >> ${rep} 2>&1
echo '-------' >> ${rep}

cat /proc/cpuinfo >> ${rep} 2>&1
echo '-------' >> ${rep}

ps fauwwx >> ${rep} 2>&1
echo '-------' >> ${rep}

/sbin/ifconfig -a >> ${rep} 2>&1
echo '-------' >> ${rep}

if [[ -x /usr/sbin/dmidecode ]]
then
  /usr/sbin/dmidecode >> ${rep} 2>&1
  echo '-------' >> ${rep}
fi

df -h >> ${rep} 2>&1
echo '-------' >> ${rep}

df -i >> ${rep} 2>&1
echo '-------' >> ${rep}

free -m >> ${rep} 2>&1
echo '-------' >> ${rep}

uptime >> ${rep} 2>&1
echo '-------' >> ${rep}

vmstat 1 10 >> ${rep} 2>&1
echo '-------' >> ${rep}

iostat >> ${rep} 2>&1
echo '-------' >> ${rep}

if [[ -x /usr/sbin/selinuxenabled ]]
then
  echo -n 'SELinux: ' >> ${rep}
  /usr/sbin/selinuxenabled 2>&1
  if [[ $? == 0 ]];
  then
    echo 'YES' >> ${rep}
  else
    echo 'NO' >> ${rep}
  fi
echo '-------' >> ${rep}
fi

/usr/sbin/ntpdate -vuq ntp.utoronto.ca >> ${rep} 2>&1
echo '-------' >> ${rep}

date >> ${rep} 2>&1
echo '-------' >> ${rep}

echo -e 'PHP:\n' >> ${rep}
php -i | grep -iE '(PHP Version|safe_m|open_b|max_(ex|in)|y_limi|suhos|apc|eacc|xcac|mmcac|memc|ioncub|^Load|gc_[dmp]|save_(path|handler)|timezone|xdebug|relic|opcache)' >> ${rep} 2>&1
php -i | grep -A 1 'memcache support' >> ${rep} 2>&1
php -i | grep -A 1 'APC Support' >> ${rep} 2>&1
echo '-------' >> ${rep}

echo -n 'MySQL client version: ' >> ${rep}
mysql -V >> ${rep} 2>&1
echo '-------' >> ${rep}


if [[ -r ../../app/etc/local.xml ]]
then

  dbhost=`cat ../../app/etc/local.xml | grep '<host>' | awk -F[ '{ print $3 }' | awk -F] '{ print $1 }'`
  #echo "dbhost = \"${dbhost}\""
  dbuser=`cat ../../app/etc/local.xml | grep '<username>' | awk -F[ '{ print $3 }' | awk -F] '{ print $1 }'`
  #echo "dbuser = \"${dbuser}\""
  dbpass=`cat ../../app/etc/local.xml | grep '<password>' | awk -F[ '{ print $3 }' | awk -F] '{ print $1 }'`
  #echo "dbpass = \"${dbpass}\""
  dbname=`cat ../../app/etc/local.xml | grep '<dbname>' | awk -F[ '{ print $3 }' | awk -F] '{ print $1 }'`
  #echo "dbname = \"${dbname}\""

  if [[ ${dbhost} != "" && ${dbhost} != "localhost" ]]
  then
    mysqlparams=${mysqlparams}"-h "${dbhost}
  fi

  mysqlparams=${mysqlparams}" -u "${dbuser}

  if [[ ${dbpass} != "" ]]
  then
    mysqlparams=${mysqlparams}" -p\"${dbpass}\""
  fi

  mysqlparams=${mysqlparams}" "${dbname}

  #echo "mysqlparams = \"${mysqlparams}\""


  echo -n 'MySQL server version: ' >> ${rep}
  echo 'SELECT VERSION()' | mysql ${mysqlparams} | grep -v 'VERSION' >> ${rep} 2>&1
  echo '-------' >> ${rep}

  echo -n 'Magento products: ' >> ${rep}
  echo 'SELECT COUNT(*) FROM catalog_product_entity' | mysql ${mysqlparams} | grep -v 'COUNT' >> ${rep} 2>&1
  echo '-------' >> ${rep}

  echo -n 'Magento categories: ' >> ${rep}
  echo 'SELECT COUNT(*) FROM catalog_category_entity' | mysql ${mysqlparams} | grep -v 'COUNT' >> ${rep} 2>&1
  echo '-------' >> ${rep}

  echo -n 'Magento customers: ' >> ${rep}
  echo 'SELECT COUNT(*) FROM customer_entity' | mysql ${mysqlparams} | grep -v 'COUNT' >> ${rep} 2>&1
  echo '-------' >> ${rep}

  echo -n 'Magento cron not-success events: ' >> ${rep}
  echo 'SELECT COUNT(*) FROM cron_schedule WHERE status != "success"' | mysql ${mysqlparams} | grep -v 'COUNT' >> ${rep} 2>&1
  echo '-------' >> ${rep}

  echo -e 'MySQL master status:\n' >> ${rep}
  echo 'SHOW MASTER STATUS' | mysql ${mysqlparams} >> ${rep} 2>&1
  echo '-------' >> ${rep}

  echo -e 'MySQL slave status:\n' >> ${rep}
  echo 'SHOW SLAVE STATUS' | mysql ${mysqlparams} >> ${rep} 2>&1
  echo '-------' >> ${rep}

  echo -e 'MySQL InnoDB status:\n' >> ${rep}
  echo 'SHOW ENGINE INNODB STATUS \G' | mysql ${mysqlparams} >> ${rep} 2>&1
  echo '-------' >> ${rep}

  echo -e 'MySQL plugins:\n' >> ${rep}
  echo 'SHOW PLUGINS' | mysql ${mysqlparams} >> ${rep} 2>&1
  echo '-------' >> ${rep}

  echo -e 'MySQL global variables:\n' >> ${rep}
  echo 'SHOW GLOBAL VARIABLES' | mysql ${mysqlparams} >> ${rep} 2>&1
  echo '-------' >> ${rep}

  echo -e 'MySQL status:\n' >> ${rep}
  echo 'SHOW STATUS' | mysql ${mysqlparams} >> ${rep} 2>&1
  echo '-------' >> ${rep}

else

  echo "Unable to read Magento app/etc/local.xml config file!"
  echo "Unable to read Magento app/etc/local.xml config file!" >> ${rep}

fi
