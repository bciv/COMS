USE [master]
GO

/****** Object:  Database [COMS_TEST_1]    Script Date: 04/24/2014 11:21:41 ******/
CREATE DATABASE [COMS_TEST_3] ON  PRIMARY 
( NAME = N'COMS_TEST_3_Data', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL.2\MSSQL\Data\COMS_TEST_3_Data.mdf' , SIZE = 16512KB , MAXSIZE = UNLIMITED, FILEGROWTH = 10%)
 LOG ON 
( NAME = N'COMS_TEST_3_Log', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL.2\MSSQL\Data\COMS_TEST_3_Log.ldf' , SIZE = 2048KB , MAXSIZE = 2048GB , FILEGROWTH = 1024KB )
GO

ALTER DATABASE [COMS_TEST_3] SET COMPATIBILITY_LEVEL = 100
GO

IF (1 = FULLTEXTSERVICEPROPERTY('IsFullTextInstalled'))
begin
EXEC [COMS_TEST_3].[dbo].[sp_fulltext_database] @action = 'enable'
end
GO

ALTER DATABASE [COMS_TEST_3] SET ANSI_NULL_DEFAULT OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET ANSI_NULLS OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET ANSI_PADDING OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET ANSI_WARNINGS OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET ARITHABORT OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET AUTO_CLOSE OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET AUTO_CREATE_STATISTICS ON 
GO

ALTER DATABASE [COMS_TEST_3] SET AUTO_SHRINK OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET AUTO_UPDATE_STATISTICS ON 
GO

ALTER DATABASE [COMS_TEST_3] SET CURSOR_CLOSE_ON_COMMIT OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET CURSOR_DEFAULT  GLOBAL 
GO

ALTER DATABASE [COMS_TEST_3] SET CONCAT_NULL_YIELDS_NULL OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET NUMERIC_ROUNDABORT OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET QUOTED_IDENTIFIER OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET RECURSIVE_TRIGGERS OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET  ENABLE_BROKER 
GO

ALTER DATABASE [COMS_TEST_3] SET AUTO_UPDATE_STATISTICS_ASYNC OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET DATE_CORRELATION_OPTIMIZATION OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET TRUSTWORTHY OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET ALLOW_SNAPSHOT_ISOLATION OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET PARAMETERIZATION SIMPLE 
GO

ALTER DATABASE [COMS_TEST_3] SET READ_COMMITTED_SNAPSHOT OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET HONOR_BROKER_PRIORITY OFF 
GO

ALTER DATABASE [COMS_TEST_3] SET  READ_WRITE 
GO

ALTER DATABASE [COMS_TEST_3] SET RECOVERY FULL 
GO

ALTER DATABASE [COMS_TEST_3] SET  MULTI_USER 
GO

ALTER DATABASE [COMS_TEST_3] SET PAGE_VERIFY CHECKSUM  
GO

ALTER DATABASE [COMS_TEST_3] SET DB_CHAINING OFF 
GO


