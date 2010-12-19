/*
 * Copyright © 2010 Orsa Studio (www.orsa-studio.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package com.orsa.slimstack.app;

import java.io.*;
import java.util.*;

public class WindowsUtils {
  public static List<String> listRunningProcesses() {
    List<String> processes = new ArrayList<String>();
    try {
      String line;
      Process p = Runtime.getRuntime().exec("tasklist.exe /fo csv /nh");
      BufferedReader input = new BufferedReader
          (new InputStreamReader(p.getInputStream()));
      while ((line = input.readLine()) != null) {
          if (!line.trim().equals("")) {
              // keep only the process name
              line = line.substring(1);
              processes.add(line.substring(0, line.indexOf("\"")));
          }

      }
      input.close();
    }
    catch (Exception err) {
      err.printStackTrace();
    }
    return processes;
  }

  public static void main(String[] args){
      List<String> processes = listRunningProcesses();
      String result = "";

      // display the result
      Iterator<String> it = processes.iterator();
      int i = 0;
      while (it.hasNext()) {
         result += it.next() +",";
         i++;
         if (i==10) {
             result += "\n";
             i = 0;
         }
      }
      msgBox("Running processes : " + result);
  }

  public static void msgBox(String msg) {
    javax.swing.JOptionPane.showConfirmDialog((java.awt.Component)
       null, msg, "WindowsUtils", javax.swing.JOptionPane.DEFAULT_OPTION);
  }
}


