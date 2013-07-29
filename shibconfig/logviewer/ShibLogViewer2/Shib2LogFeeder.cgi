#! /usr/bin/perl -w
use strict;
use CGI qw(:standard);
use CGI::Carp qw(warningsToBrowser fatalsToBrowser);

my ($class,$configFile,$curpos,$indent,$ip,$line,$linecount,$logFile,$message,$pos,$time,$ts) = '';
my (%hosts,@lines) = ();

######################################################
#           CHANGE THE FOLLOWING VARIABLES           #
######################################################

# Shibboleth log file
# For Service Provider
$logFile = "/var/log/shibboleth/shibd.log";

# For Identity Provider
#$logFile = "/var/log/shibboleth/idp-process.log";

# Remember to set the log Level to DEBUG and if you
# want to see assertions on the IdP, make sure to activate
# also the PROTOCOL_MESSAGE logger

# File where IP, timestamp and position is stored
$configFile = "/tmp/log-position.txt";
######################################################

print header;

open(LOGFILE, $logFile) or die("Error while opening log file: $!\n");

if (!(-e $configFile )){
	open(CONFIGFILE, ">".$configFile);
	print CONFIGFILE '0';
	close(CONFIGFILE);
}

open(CONFIGFILE, $configFile) or die "Error while opening config file '$configFile': $!\n";
while(defined(my $i = <CONFIGFILE>)){
	# Get position in bytes
	if ($i =~ /([0-9\.]+)\s+(\d+)\s+(\d+)/){
		$ip = $1;
		$pos = $2;
		$ts= $3;
		
		# Remove old entries
		if ((time - $ts) < 86400){
			$hosts{$ip}{'position'} = $pos;
			$hosts{$ip}{'time'} = $ts;
		}
	} 
}
close(CONFIGFILE);

if (
	$hosts{$ENV{'REMOTE_ADDR'}}
	&& (time - $hosts{$ENV{'REMOTE_ADDR'}}{'time'}) < 120
	){
	$curpos = $hosts{$ENV{'REMOTE_ADDR'}}{'position'};
	
	# Go to right position
	seek(LOGFILE, $curpos , 0);
	
	# Update time stamp
	$hosts{$ENV{'REMOTE_ADDR'}}{'time'} = time();
} else {
	seek(LOGFILE, 0 , 2);
	$curpos = tell(LOGFILE);
	$curpos -= 1000;
	
	# Go to right position
	seek(LOGFILE, $curpos , 0);
	
	# Read one line
	$line = <LOGFILE>;
	
	# List host
	$hosts{$ENV{'REMOTE_ADDR'}}{'time'} = time();
}

# 18:30:59.106 DEBUG [org.opensaml.saml1.binding.encoding.HTTPSOAP11Encoder:120] 
while(defined($line = <LOGFILE>)) {
	if ($line =~ /([:0-9]+)\.\d+\s([\w]+)\s\[([^\s]+)\]\s-\s(.*)/){
		
		$time = $1;
		$class = lc($2);
		#$component = $3;
		$message = $4;
		$message =~ s/(.{90,120}[\s\$\.,:-])/$1\n         /g;
		$line = "<span class=\"$class\"><i>$time</i> <b>$message</b></span>";
		
		
		print $line."\n";
		$linecount = 1;
		$indent = '';
		
	} else {
		$line =~ s/</\n</g;
		$line =~ s/\s([\S]+)="([^"]+)"/\n$1="$2"/g;
		$line =~ s/([^\?])>(\S+)/$1>\n$2/g;
		$line =~ s/^\s+(.+)/$1/g;
		$line =~ s/(.{80,120}[ ,:-])/$1\n/g;
		
		
		@lines = split(/\n/, $line);
		#$reduce_indent;
		foreach my $sline (@lines){
			
			print '<i>'.sprintf("%3d ",$linecount).'</i> ';
			
			
			if ($sline =~ /</g && $sline !~ /\/>/g && $sline !~ /<\//g){
				$indent .= '    ';
			}
			
			
			$sline =~ s/</&lt;/g;
			$sline =~ s/>/&gt;/g;
			
			#$line = $sline."\n";
			if ($sline =~ /&lt;/){
				$line = "<span class=\"element\">".$sline."</span>\n";
			} elsif ($sline =~ /(.+)="(.+)"(.*)/){
				$line = "  <span class=\"attribute\">$1</span>=<span class=\"value\">&quot;$2&quot;</span><span class=\"element\">$3</span>\n";
			} else {
				$line = "    <span class=\"value\">".$sline."</span>\n";
			}
			
			print $indent.$line;
			
			if ($sline =~ /\&lt;\//g){
				$indent = substr($indent, 0, -4);
			} elsif($sline =~ /[\/\?]&gt;/g){
				$indent = substr($indent, 0, -4);
			} 
			
			$linecount++;
		}
	}
}

# Save position
$curpos = tell(LOGFILE);
$hosts{$ENV{'REMOTE_ADDR'}}{'position'} = $curpos;

# Write position
open(CONFIGFILE,"> ".$configFile ) or die "Error while opening config file '$configFile': $!\n";
foreach my $host (keys(%hosts)){
	 print CONFIGFILE $host." ".$curpos." ".$hosts{$host}{'time'}."\n";
}
close(CONFIGFILE);
