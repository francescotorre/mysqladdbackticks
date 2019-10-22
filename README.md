# MySqliAddBacktick

## Author: Francesco Torre

## Description

This is **utility software** written in **PHP** for **MySql**.

In MySql, **field/table names** are usually enclosed in **backticks** to avoid conflicts with **reserved keywords**, but the **backtick key** is not available on some keyboard layouts, and hence this program, which **takes a query in input and automatically returns the same query in output with backticks placed where needed**.

For example, given the following input:

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>select employee.firstname,</code>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>employee.lastname from users</code>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>where employee.id between 30 and 50</code>

returns as output (when **beautify** is **on**):

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>SELECT</code>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>\`employee\`.\`firstname\`,</code>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>\`employee\`.\`lastname\`</code>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>FROM</code>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>\`users\`</code>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>WHERE</code>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>\`employee\`.\`id\` BETWEEN 30 AND 50</code>

I haven't fully tested the software with complex queries (sub-selects, unions, etc.), but it seems to work pretty well for common cases.

Standard statements **SELECT**, **INSERT**, **UPDATE** and **DELETE** are fully supported.

## Demo

**You can try this application online at**:

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[link](#)

In the page, move the mouse over the **info** icon to see instructions on how to use it.

## Requirements

**PHP 5.x**.

*It doesn't work with PHP 7.x.*

## Features

- Supports the most common MySql reserved keywords

- Supports using most of the reserved keywords as table/field names

- Distinguishes between reserved keywords in expressions and string values

- Basic output beautification

## Security

Built-in security against SQL Injection.

## Notes

In some cases, I have implemented a very complex logic to achieve the desired result.

One could argue that there is a simpler way to achieve the same result.

The fact is, I challenged myself to write code in such a way a developer has the opportunity (to a certain degree) to name tables and fields using reserved keywords (if he so wishes).

I.e. table/field **\`into\`**, **\`select\`**, etc.

MySql allows you to do that, but requires those names to be enclosed in **backticks**.

