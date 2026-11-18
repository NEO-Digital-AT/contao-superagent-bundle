-- Sample records for the documentation images (see README.md). Run once against the database of the test
-- installation, after the assistants exist:
--   mysql contao < tools/docs-images/sample.sql
-- No request leaves the installation: the connection is never contacted, its tools are stored as if read.
SET NAMES utf8mb4;

-- A connection with four tools: one that reads (released) and one each that changes data, deletes and is not marked
-- by the server (locked as they first appear). Confirmed, as after the first release.
INSERT INTO tl_neo_agent_connection (tstamp, name, url, auth, personal_data, tools, locked, confirmed_by, confirmed_at)
VALUES (1794991320, 'Appointments', 'https://mcp.example.com/mcp', 'none', 'mask', '{"read_at":1794991200,"url":"https://mcp.example.com/mcp","server":"appointments","tools":[{"name":"list_free_slots","title":"","description":"Lists the free appointment slots of a day.","inputSchema":{"type":"object","properties":{"date":{"type":"string","description":"Day, YYYY-MM-DD"}},"required":["date"]},"annotations":{"readOnlyHint":true}},{"name":"book_appointment","title":"","description":"Books a slot for a customer.","inputSchema":{"type":"object","properties":{"slot":{"type":"string"},"first_name":{"type":"string"},"last_name":{"type":"string"},"email":{"type":"string"}},"required":["slot","last_name","email"]},"annotations":{"readOnlyHint":false,"destructiveHint":false}},{"name":"cancel_appointment","title":"","description":"Cancels a booked appointment.","inputSchema":{"type":"object","properties":{"booking_number":{"type":"string"}},"required":["booking_number"]},"annotations":{"readOnlyHint":false,"destructiveHint":true}},{"name":"list_services","title":"","description":"Lists the services that can be booked, with their duration.","inputSchema":{"type":"object","properties":{}}}],"new":[]}',
  'a:3:{i:0;s:16:"book_appointment";i:1;s:18:"cancel_appointment";i:2;s:13:"list_services";}', 'admin', 1794991320);
SET @connection = LAST_INSERT_ID();

-- Every assistant may use the tool that reads.
UPDATE tl_neo_agent SET mcp_tools = CONCAT('a:1:{i:0;s:', CHAR_LENGTH(CONCAT(@connection, ':list_free_slots')), ':"', @connection, ':list_free_slots";}');

-- A skill loaded from GitHub and checked, not taken over yet.
INSERT INTO tl_neo_agent_skill (tstamp, source, url, name, description, body, pending)
VALUES (1794991500, 'github', 'https://github.com/owner/repo/tree/main/skills/appointments', '', '', '', '{"file":{"name":"appointments","description":"Load when the visitor asks for an appointment or a free slot.","body":"# Appointments\\n\\nUse this skill when the visitor wants an appointment.\\n\\n1. Ask for the day if the visitor has not named one.\\n2. Call list_free_slots with that day and offer at most three slots.\\n3. The team books the appointment: open the forwarding form with the chosen slot in the message.\\n\\nNever promise a slot as booked.","tools":["list_free_slots"],"depends":[]},"hash":"0000000000000000000000000000000000000000","fetched_at":1794991500,"files":[],"check":{"errors":[],"warnings":[]}}');

-- Two calls in the tool log of the English assistant: a locked tool that was refused, a released one that ran.
SET @agent = (SELECT pid FROM tl_neo_agent_language WHERE language = 'en' ORDER BY id LIMIT 1);
INSERT INTO tl_neo_agent_tool_log (tstamp, agent, connection, tool, status, note, duration) VALUES
  (1794994980, @agent, @connection, 'list_free_slots', 'ok', '', 412),
  (1794995040, @agent, @connection, 'book_appointment', 'refused', 'locked', 0);
